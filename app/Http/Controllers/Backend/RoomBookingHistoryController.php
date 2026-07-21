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



class RoomBookingHistoryController extends Controller
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
    $perPage       = (int) $request->get('per_page', 10);
    $page          = (int) $request->get('page', 1);
    $search        = trim($request->get('search', ''));
    $startDate     = $request->get('start_date');
    $endDate       = $request->get('end_date');
    $selectedGuest = $request->get('selected_guest');

    $query = RoomBookingHistory::with(['division:id,name', 'district:id,name', 'thana:id,name'])
        ->where('status', 0);

    // Default: today data
    if (empty($startDate) && empty($endDate)) {
        $query->whereDate('created_at', today());
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->orWhere('full_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    if (!empty($selectedGuest)) {
        $query->where('full_name', $selectedGuest);
    }

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
            'father_name',
            'mother_name',
            'email',
            'phone',
            'nid',
            'mother_nid',
            'father_nid',
            'room_number',
            'payment_amount_total',
            'check_in',
            'check_out',
            'status',
            'created_at',
            'pay_cash_in',
            'pay_online',
            'daybytotalamount',
            'user_type',
        ]), [
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
        $mapped->count(),
        $perPage,
        $page,
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
        'phone' => 'required|regex:/^01[3-9][0-9]{8}$/',
        'email'       => 'nullable|email|max:150',
        'mother_nid'  => 'nullable|string|max:50',
        'father_nid'  => 'nullable|string|max:50',
        'father_name' => 'nullable|string|max:150',
        'mother_name' => 'nullable|string|max:150',
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
    $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
    $existingUser = null;
    if ($request->filled('email')) {

        $existingUser = User::where('email', $request->email)
            ->where('status', 1)
            ->first();
    }
    if (!$existingUser) {
        $existingUser = User::where('status', 1)
            ->where(function ($q) use ($request, $cleanPhone) {

                $q->where('phone', $request->phone)
                  ->orWhere('phone', $cleanPhone);

            })->first();
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
    // temp password check
    if (
        !empty($existingUser->temp_password) &&
        $request->password == $existingUser->temp_password
    ) {
        $passwordMatched = true;
    }
    // hashed password check
    if (
        !$passwordMatched &&
        !empty($existingUser->password)
    ) {
        $passwordMatched = Hash::check(
            $request->password,
            $existingUser->password
        );
    }
    if (!$passwordMatched) {
        if ($request->ajax()) {
            return response()->json([
                'status'         => false,
                'message'        => 'Password did not match. Please enter the correct password. (Maximum 6 digits allowed)',
                'wrong_password' => true,
            ], 422);
        }
        return back()
            ->with('error', 'Password did not match. Please enter the correct password. (Maximum 6 digits allowed)')
            ->withInput();
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
            $parts = explode('-', $roomNumber, 2);
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

            $selectedRoomNumbers[] = $room->room_no;
            $selectedAcStatus[]    = $room->ac_status;
            $floorNames[]          = $floorName;

            $roomJsonData[] = [
                'floornumber' => (string) $floorName,
                'roomnumber'  => (string) $roomNumber,
                'price'       => $singleRoomPrice,
            ];
            $totalAmount += $singleRoomPrice;
        }
        $checkInDate      = Carbon::parse($request->check_in);
        $checkOutDate     = Carbon::parse($request->check_out);
        $numberOfDays     = $checkInDate->diffInDays($checkOutDate);
        if ($numberOfDays < 1) {
            $numberOfDays = 1;
        }
        $dayByTotalAmount = $totalAmount * $numberOfDays;
        if ($existingUser) {
            $existingUser->update([
                'name'   => $request->full_name ?: $existingUser->name,
                'email'  => $request->filled('email') ? $request->email : $existingUser->email,
                'phone'  => $request->phone ?: $existingUser->phone,
                'status' => 1,
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name'          => $request->full_name,
                'email'         => $request->filled('email') ? $request->email : null,
                'phone'         => $request->phone,
                'password'      => bcrypt($tempPassword),
                'status'        => 1,
                'temp_password' => $tempPassword,
            ]);
            $role = Role::firstOrCreate([
                'name'       => 'HotelGuest',
                'guard_name' => 'web'
            ]);
            $user->assignRole($role);
        }
        RoomBookingHistory::create([
            'image'                              => $imagePath,
            'floor_number_room_number_roomprice' => $roomJsonData,
            'full_name'                          => $request->full_name,
            'user_type'                          => $request->user_type ?? 'student',
            'email' => $request->filled('email') ? $request->email : null,
            'phone'                              => '+88' . ltrim($request->phone, '+88'),
            'nid'                                => $request->nid ?: $request->mother_nid,
            'mother_nid'                         => $request->mother_nid,
            'father_nid'                         => $request->father_nid,
            'father_name'                        => $request->father_name,
            'mother_name'                        => $request->mother_name,
            'password'                           => $existingUser ? $request->password : $tempPassword,
            'division_id'                        => $request->division_id,
            'district_id'                        => $request->district_id,
            'thana_id'                           => $request->thana_id,
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
        $adminEmail = 'mr2798492@gmail.com';
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
            'daybytotalamount' => $dayByTotalAmount, 
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
    
    if (!empty($request->email)) {
        Mail::to($request->email)->send(new RoomBookingMail($mailData));
    }
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
            'nid'         => $guest->mother_nid,
            'mother_nid'  => $guest->mother_nid,
            'father_nid'  => $guest->father_nid,
            'father_name' => $guest->father_name,
            'mother_name' => $guest->mother_name,
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

        // Guest filter by full_name
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
                'father_name',
                'mother_name',
                'email',
                'phone',
                'nid',
                'mother_nid',
                'father_nid',
                'room_number',
                'payment_amount_total',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'daybytotalamount',
                'user_type',
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

    // HotelGuest — শুধু নিজের booking
    $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
    
    $query = RoomBookingHistory::where(function ($q) use ($user, $cleanPhone) {
            if (!empty($user->email)) {
                $q->orWhere('email', $user->email);
            }
            if (!empty($cleanPhone)) {
                $q->orWhere('phone', 'like', "%{$cleanPhone}%");
            }
        })->with(['division', 'district', 'thana']);

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            return array_merge($row->only([
                'id',
                'image',
                'full_name',
                'father_name',
                'mother_name',
                'email',
                'phone',
                'nid',
                'mother_nid',
                'father_nid',
                'room_number',
                'payment_amount_total',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'daybytotalamount',
                'user_type',
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