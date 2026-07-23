<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Backend\Division;
use App\Models\Backend\District;
use App\Models\Backend\Reception;
use App\Models\Backend\Thana;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Mail\RoomBookingMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Spatie\Permission\Models\Role;  
use App\Models\Backend\RoomSeat;

class BookingConttoller extends Controller
{
    
 public function getDivisions()
    {
        $divisions = Division::all();
        return response()->json($divisions);
    }


    public function getDistrictsByDivision($divisionId)
    {
        $districts = District::where('division_id', $divisionId)->get();
        return response()->json($districts);
    }

 
    public function getThanasByDistrict($districtId)
    {
        $thanas = Thana::where('district_id', $districtId)->get();
        return response()->json($thanas);
    }

    public function getroombookinghistory()

    {

        return view('backend.roombooking.roombooking');

    }

    public function todaygetroombookinghistory()
    {
        return view('backend.roombooking.todayroombooking');
    }


    public function todaygetbookinghistory(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));

        $query = RoomBookingHistory::with(['division:id,name','district:id,name','thana:id,name'])
            ->where('status', 0)
            ->whereDate('created_at', today());

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                foreach (['full_name','phone'] as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            return array_merge($row->only(['id','image','full_name','email','phone','nid','room_number','payment_amount_total','check_in','check_out','status','created_at','pay_cash_in','pay_online']), [
                'group_key'     => 'booking_' . $row->id,
                'floornumber'   => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'    => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'         => $c->pluck('price')->filter()->implode(', '),
                'room_items'    => $items,
                'division_name' => optional($row->division)->name ?? '-',
                'district_name' => optional($row->district)->name ?? '-',
                'thana_name'    => optional($row->thana)->name ?? '-',
            ]);
        })->values();

        $paginator = new LengthAwarePaginator(
            $mapped->slice(($page - 1) * $perPage, $perPage)->values(),
            $mapped->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginator);
    }
    
public function store(Request $request)
{
    foreach (['check_in', 'check_out'] as $f) {
        $v = $request->input($f);
        if ($v && preg_match('/^\d{2}-\d{2}-\d{4}$/', $v)) {
            $request->merge([
                $f => Carbon::createFromFormat('d-m-Y', $v)->format('Y-m-d')
            ]);
        }
    }

    $roomsRaw = $request->input('rooms');
    $roomsPayload = [];

    if (is_array($roomsRaw)) {
        $roomsPayload = $roomsRaw;
    } else {
        $decoded = json_decode((string) $roomsRaw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $roomsPayload = $decoded;
        }
    }

    $normalizedRooms = [];
    foreach ($roomsPayload as $item) {
        if (is_array($item) && !empty($item['roomnumber'])) {
            $normalizedRooms[] = [
                'floornumber' => (string) ($item['floornumber'] ?? ''),
                'roomnumber'  => (string) $item['roomnumber'],
                'price'       => (float) ($item['price'] ?? 0),
                'advance_price' => (float) ($item['advance_price'] ?? 0),
            ];
        }
    }

    $roomNumbersOnly = collect($normalizedRooms)
        ->pluck('roomnumber')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    $request->merge([
        'rooms_payload' => $normalizedRooms,
        'rooms_list'    => $roomNumbersOnly,
    ]);

    $validator = Validator::make($request->all(), [
        'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:2048',
        'check_in'    => 'required|date_format:Y-m-d',
        'check_out'   => 'required|date_format:Y-m-d|after_or_equal:check_in',
        'full_name'   => 'required|string|max:150',
        'phone'       => 'required|string|max:30',
        'email'       => 'nullable|email|max:150',
        'nid'         => 'nullable|string|max:50',
        'password'    => 'nullable|string|max:50',
        'division_id' => 'nullable|numeric',
        'district_id' => 'nullable|numeric',
        'thana_id'    => 'nullable|numeric',
        'payment'     => 'required|in:online,cash',
        'pay_method'  => 'nullable|string|max:30',
        'trx'         => 'nullable|string|max:80',
    ]);

    $validator->sometimes(['trx', 'pay_method'], 'required|min:2', function ($input) {
        return $input->payment === 'online';
    });

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }
        return back()->withErrors($validator)->withInput();
    }

    $adminEmail = 'mr2798492@gmail.com';
    $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
    $existingUser = null;
    if ($request->filled('email')) {
        $existingUser = User::where('email', $request->email)->first();
    }
    if (!$existingUser) {
        $existingUser = User::where('phone', $request->phone)
            ->orWhere('phone', $cleanPhone)
            ->first();
    }
    $accountCreated = false;
    $tempPassword   = null;
    $user           = null;
    if ($existingUser && !$request->filled('password')) {
        if ($request->ajax()) {
            return response()->json([
                'status'               => false,
                'message'              => ' Account  already exists. Please enter your password.',
                'account_exists_alert' => true,
            ], 422);
        }
        return back()->with('error', 'Account already exists. Please enter your password.')->withInput();
    }
    if ($existingUser && $request->filled('password')) {
        $passwordMatched = false;
        if (!empty($existingUser->password)) {
            $passwordMatched = Hash::check($request->password, $existingUser->password);
        }
        if (!$passwordMatched && !empty($existingUser->temp_password)) {
            $passwordMatched = $request->password === $existingUser->temp_password;
        }
        if (!$passwordMatched) {
            if ($request->ajax()) {
                return response()->json([
                    'status'         => false,
                    'message'        => 'Password did not match. Please enter the correct password.',
                    'wrong_password' => true,
                ], 422);
            }
            return back()->with('error', 'Password did not match. Please enter the correct password.')->withInput();
        }
        $user = $existingUser;
    }

    if (!$existingUser) {
        $tempPassword = $request->filled('password')
            ? $request->password
            : strtoupper(Str::random(8));
        $accountCreated = true;
    }
    $imagePath = null;
    if ($request->hasFile('image')) {
        $file     = $request->file('image');
        $ext      = $file->getClientOriginalExtension();
        $fileName = $cleanPhone . '.' . $ext;
        $folder   = public_path('bookingsimage');
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        if (file_exists($folder . '/' . $fileName)) {
            @unlink($folder . '/' . $fileName);
        }
        $file->move($folder, $fileName);
        $imagePath = $fileName;

    } elseif ($request->filled('existing_image')) {
        $imagePath = $request->existing_image;
    }

    DB::beginTransaction();

    try {
        $selectedRoomNumbers = [];
        $selectedAcStatus    = [];
        $roomJsonData        = [];
        $totalAmount         = 0;
        $floorNames          = [];

        foreach ($request->rooms_list as $roomNumber) {
            $parts = explode('-', $roomNumber);
            $roomNo = $parts[0] ?? '';
            $seatNo = $parts[1] ?? '';

            $room = Room::with('floor')
                ->where('room_no', $roomNo)
                ->first();

            if (!$room) {
                throw new \Exception("Room not found: {$roomNo}");
            }

            $seat = RoomSeat::where('room_id', $room->id)
                ->where('seat_no', $seatNo)
                ->lockForUpdate()
                ->first();

            if (!$seat) {
                throw new \Exception("Seat not found: {$seatNo} in Room {$roomNo}");
            }

            if ((int) $seat->status === 1) {
                throw new \Exception("Seat already booked: {$roomNumber}");
            }

            $singleRoomPrice = (float) ($seat->price ?? 0);
            $floorName       = $room->floor->name ?? '';

            $seat->update(['status' => 1]);
            Room::syncRoomStatus($room->id);

            $selectedRoomNumbers[] = $roomNumber;
            $selectedAcStatus[]    = $room->ac_status;
            $floorNames[]          = $floorName;

            $roomJsonData[] = [
                'floornumber' => (string) $floorName,
                'roomnumber'  => (string) $roomNumber,
                'price'       => $singleRoomPrice,
                'advance_price' => (float) ($seat->advance_price ?? 0),
            ];

            $totalAmount += $singleRoomPrice;
        }

        // $totalAmount loop 
        $checkInDate      = Carbon::parse($request->check_in);
        $checkOutDate     = Carbon::parse($request->check_out);
        $numberOfDays     = $checkInDate->diffInDays($checkOutDate);

        if ($numberOfDays < 1) {
            $numberOfDays = 1;
        }

        $monthsCount = max(1, (int) round($numberOfDays / 30));
        $advanceTotal = 0;
        foreach ($roomJsonData as $item) {
            $advanceTotal += (float) ($item['advance_price'] ?? 0);
        }

        $dayByTotalAmount = ($totalAmount * $monthsCount) + $advanceTotal;

        if ($existingUser) {
            $existingUser->update([
                'name'       => $request->full_name ?: $existingUser->name,
                'email'      => $request->email ?: $existingUser->email,
                'phone'      => $request->phone ?: $existingUser->phone,
                'address'    => $request->address ?: $existingUser->address,
                'user_image' => $imagePath ?: $existingUser->user_image,
                'status'     => 1,
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name'          => $request->full_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'address'       => $request->address,
                'password'      => bcrypt($tempPassword),
                'user_image'    => $imagePath,
                'status'        => 1,
                'temp_password' => $tempPassword,
            ]);

            $role = Role::firstOrCreate([
                'name'       => 'HotelGuest',
                'guard_name' => 'web'
            ]);

            $user->assignRole($role);
        }

        if ($request->filled('user_type')) {
            $formattedUserType = ucfirst(str_replace('_', ' ', $request->user_type));
            $typeRole = Role::firstOrCreate([
                'name'       => $formattedUserType,
                'guard_name' => 'web'
            ]);
            $user->assignRole($typeRole);
        }

        RoomBookingHistory::create([
            'image'                              => $imagePath,
            'floor_number_room_number_roomprice' => $roomJsonData,
            'full_name'                          => $request->full_name,
            'user_type'                          => $request->user_type ?? 'student',
            'father_name'                        => $request->father_name,
            'mother_name'                        => $request->mother_name,
            'father_nid'                         => $request->father_nid,
            'mother_nid'                         => $request->mother_nid,
            'email'                              => $request->email,
            'phone'                              => '+88' . ltrim($request->phone, '+88'),
            'nid'                                => $request->nid,
            'password'                           => $existingUser ? $request->password : $tempPassword,
            'division_id'                        => $request->division_id,
            'district_id'                        => $request->district_id,
            'thana_id'                           => $request->thana_id,
            'address'                            => $request->address,
            'pay_cash_in'                        => $request->payment === 'cash' ? 'cash' : null,
            'pay_online'                         => $request->payment === 'online'
                                                    ? (($request->pay_method ?? 'Online') . ' | TRX: ' . ($request->trx ?? ''))
                                                    : null,
            'payment_amount_total'               => $totalAmount,      
            'daybytotalamount'                   => $dayByTotalAmount,  
            'check_in'                           => $request->check_in,
            'check_out'                          => $request->check_out,
            'status'                             => 0,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        DB::commit();

        $divisionName    = optional(Division::find($request->division_id))->name ?? '-';
        $districtName    = optional(District::find($request->district_id))->name ?? '-';
        $thanaName       = optional(Thana::find($request->thana_id))->name ?? '-';
        $bookingDateTime = Carbon::now('Asia/Dhaka')->format('d/m/Y g:i A');

        $mailData = [
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'user_type'    => $request->user_type ?? 'student',
            'nid'          => $request->nid ?: $request->mother_nid,
            'mother_nid'   => $request->mother_nid,
            'father_nid'   => $request->father_nid,
            'father_name'  => $request->father_name,
            'mother_name'  => $request->mother_name,
            'floor_name'   => implode(', ', array_unique(array_filter($floorNames))),
            'room_numbers' => implode(', ', $selectedRoomNumbers),
            'room_acstatus'=> implode(', ', $selectedAcStatus),
            'check_in'     => $request->check_in,
            'check_out'    => $request->check_out,
            'total_amount' => $totalAmount,
            'division_name'=> $divisionName,
            'district_name'=> $districtName,
            'thana_name'   => $thanaName,
            'payment_type' => $request->payment,
            'pay_method'   => $request->pay_method,
            'trx'          => $request->trx,
            'image_file'   => $imagePath,
            'create_at'    => $bookingDateTime,
            'room_json'    => $roomJsonData,
        ];

        try {
            Mail::to($adminEmail)->send(new RoomBookingMail($mailData));
        } catch (\Throwable $mailError) {
            \Log::error('Room booking email failed: ' . $mailError->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'status'       => true,
                'message'      => 'Booking successfully!',
                'redirect_url' => url('/backend/dashboard'),
            ], 200);
        }

        return redirect('/backend/dashboard')->with('success', 'Booking successfully!');

    } catch (\Throwable $e) {
        DB::rollBack();

        if ($request->ajax()) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return back()->with('error', $e->getMessage())->withInput();
    }
}

        // Controller method
    public function getGuestByPhone($phone)
    {
        $digits = preg_replace('/\D/', '', $phone);
        $last11 = substr($digits, -11);

        $guest = RoomBookingHistory::whereRaw(
            "RIGHT(REPLACE(REPLACE(phone, '+', ''), ' ', ''), 11) = ?",
            [$last11]
        )->latest()->first();

        if (!$guest) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'       => true,
            'full_name'   => $guest->full_name,
            'email'       => $guest->email,
            'nid'         => $guest->nid,
            'division_id' => $guest->division_id,
            'district_id' => $guest->district_id,
            'thana_id'    => $guest->thana_id,
            'image_url'   => $guest->image ? asset('bookingsimage/' . $guest->image) : null,
            'image_name'  => $guest->image,
        ]);
    }



public function getbookinghistory(Request $request)
{
    $user = Auth::user();

    $perPage       = (int) $request->get('per_page', 10);
    $page          = (int) $request->get('page', 1);
    $search        = trim($request->get('search', ''));
    $startDate     = $request->get('start_date');
    $endDate       = $request->get('end_date');
    $selectedGuest = $request->get('selected_guest');

    if ($user->hasRole('admin')) {

        $query = RoomBookingHistory::with(['division', 'district', 'thana']);

       if ($search !== '') {
    $query->where(function ($q) use ($search) {
        foreach ([
       
            'full_name',
            'phone',
          
        ] as $col) {
            $q->orWhere($col, 'like', "%{$search}%");
        }
    });
}
        if (!empty($selectedGuest)) {
            $query->where('full_name', $selectedGuest);
        }

        // Date filter
        if (!empty($startDate) && empty($endDate)) {
            $query->whereDate('check_in', '=', $startDate);
        } elseif (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('check_in', [$startDate, $endDate]);
        }

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            return array_merge($row->only([
                'id',
                'image',
                'full_name',
                'email',
                'phone',
                'nid',
                'room_number',
                'payment_amount_total',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'daybytotalamount',
            ]), [
                'group_key'     => 'booking_' . $row->id,
                'floornumber'   => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'    => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'         => $c->pluck('price')->filter()->implode(', '),
                'room_items'    => $items,
                'division_name' => optional($row->division)->name,
                'district_name' => optional($row->district)->name,
                'thana_name'    => optional($row->thana)->name,
            ]);
        });

        $paginator = new LengthAwarePaginator(
            $mapped->slice(($page - 1) * $perPage, $perPage)->values(),
            $mapped->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginator);

    } else {

        $query = RoomBookingHistory::where('email', $user->email)
            ->with(['division', 'district', 'thana']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                foreach ([
                    'full_name',
                    'phone',
                ] as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            return array_merge($row->only([
                'id',
                'image',
                'full_name',
                'email',
                'phone',
                'nid',
                'room_number',
                'payment_amount_total',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'daybytotalamount',
            ]), [
                'group_key'     => 'booking_' . $row->id,
                'floornumber'   => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'    => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'         => $c->pluck('price')->filter()->implode(', '),
                'room_items'    => $items,
                'division_name' => optional($row->division)->name,
                'district_name' => optional($row->district)->name,
                'thana_name'    => optional($row->thana)->name,
            ]);
        });

        $paginator = new LengthAwarePaginator(
            $mapped->slice(($page - 1) * $perPage, $perPage)->values(),
            $mapped->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginator);
    }
}

    public function getNameguet()
    {
        $guests = RoomBookingHistory::select('full_name')
            ->whereNotNull('full_name')
            ->where('full_name', '!=', '')
            ->distinct()
            ->orderBy('full_name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $guests,
        ]);
    }
}
