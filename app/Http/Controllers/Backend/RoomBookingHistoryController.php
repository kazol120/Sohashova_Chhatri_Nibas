<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\Room;
use App\Models\Backend\Floor;
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
use App\Models\Backend\RoomChangeHistory;



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

    // Default: today data (check_in date)
    if (empty($startDate) && empty($endDate)) {
        $query->whereDate('check_in', today());
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
            'father_phone',
            'mother_phone',
            'room_number',
            'monthly_amount',
            'roomprice',
            'advance_fee',
            'development_fee',
            'check_in',
            'check_out',
            'status',
            'created_at',
            'pay_cash_in',
            'pay_online',
            'user_type',
            'institution_name',
            'education_level',
            'education_class',
            'workplace_name',
        ]), [
            'development_fee' => (float) ($row->development_fee ?? 0),
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
        'check_out'   => 'nullable|date_format:Y-m-d',
        'full_name'   => 'required|string|max:150',
        'phone' => 'required|regex:/^01[3-9][0-9]{8}$/',
        'email'            => 'nullable|email|max:150',
        'institution_name' => 'nullable|string|max:191',
        'education_level'  => 'nullable|string|max:191',
        'education_class'  => 'nullable|string|max:191',
        'workplace_name'   => 'nullable|string|max:191',
        'mother_nid'       => 'nullable|string|max:50',
        'father_nid'  => 'nullable|string|max:50',
        'father_phone' => 'nullable|string|max:20',
        'mother_phone' => 'nullable|string|max:20',
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
    $isResetPassword = $request->boolean('is_reset_password');

    if ($existingUser && !$request->filled('password')) {
        if ($request->ajax()) {
            return response()->json([
                'status'               => false,
                'message'              => 'Account already exists. Please enter your password or check "Forgot / Set New Password".',
                'account_exists_alert' => true,
            ], 422);
        }
        return back()->with('error', 'Account already exists. Please enter your password.')->withInput();
    }

    if ($existingUser && $request->filled('password')) {
        if ($isResetPassword) {
            $tempPassword = $request->password;
            $existingUser->update([
                'password'      => bcrypt($request->password),
                'temp_password' => $request->password,
            ]);
            $user = $existingUser;
        } else {
            $passwordMatched = false;
            if (
                !empty($existingUser->temp_password) &&
                $request->password == $existingUser->temp_password
            ) {
                $passwordMatched = true;
            }
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
                        'message'        => 'Password did not match. If you forgot your password, please check "Forgot / Set New Password".',
                        'wrong_password' => true,
                    ], 422);
                }
                return back()
                    ->with('error', 'Password did not match. If you forgot your password, please check "Forgot / Set New Password".')
                    ->withInput();
            }
            $user = $existingUser;
            $tempPassword = $request->password;
        }
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

        $singleRoomPrice    = (float) ($seat->price ?? 0);           
        $singleAdvancePrice = (float) ($seat->advance_price ?? 0);  
        $floorName          = $room->floor->name ?? '';

            $seat->update(['status' => 1]);

            $selectedRoomNumbers[] = $room->room_no;
            $selectedAcStatus[]    = $room->ac_status;
            $floorNames[]          = $floorName;

            $roomJsonData[] = [
                'floornumber' => (string) $floorName,
                'roomnumber'  => (string) $roomNumber,
                'advance_price' => $singleAdvancePrice,
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
        $userTypeRole = $request->filled('user_type') ? ucfirst(str_replace('_', ' ', $request->user_type)) : 'Student';
        $role = Role::firstOrCreate([
            'name'       => $userTypeRole,
            'guard_name' => 'web'
        ]);

        if ($existingUser) {
            $existingUser->update([
                'name'       => $request->full_name ?: $existingUser->name,
                'email'      => $request->filled('email') ? $request->email : $existingUser->email,
                'phone'      => $request->phone ?: $existingUser->phone,
                'address'    => $request->address ?: $existingUser->address,
                'user_image' => $imagePath ?: $existingUser->user_image,
                'status'     => 1,
            ]);
            $existingUser->syncRoles([$role]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name'          => $request->full_name,
                'email'         => $request->filled('email') ? $request->email : null,
                'phone'         => $request->phone,
                'address'       => $request->address,
                'password'      => bcrypt($tempPassword),
                'user_image'    => $imagePath,
                'status'        => 1,
                'temp_password' => $tempPassword,
            ]);
            $user->syncRoles([$role]);
        }
        $appSettings = \App\Services\SettingService::getSettingContentBySlug('app_setting');
        $devFeeActive = ($appSettings['development_fee_status'] ?? '1') == '1';
        $devFee = $devFeeActive ? (float) ($appSettings['development_fee'] ?? 3000) : 0.00;

        RoomBookingHistory::create([
            'image'                              => $imagePath,
            'floor_number_room_number_roomprice' => $roomJsonData,
            'full_name'                          => $request->full_name,
            'user_type'                          => $request->user_type ?? 'student',
            'institution_name'                   => $request->institution_name,
            'education_level'                    => $request->education_level,
            'education_class'                    => $request->education_class,
            'workplace_name'                     => $request->workplace_name,
            'email'                              => $request->filled('email') ? $request->email : null,
            'phone'                              => '+88' . ltrim($request->phone, '+88'),
            'nid'                                => (strtolower($request->user_type ?? 'student') === 'student') ? null : $request->nid,
            'mother_nid'                         => $request->mother_nid,
            'father_nid'                         => $request->father_nid,
            'father_phone'                       => $request->father_phone,
            'mother_phone'                       => $request->mother_phone,
            'father_name'                        => $request->father_name,
            'mother_name'                        => $request->mother_name,
            'password'                           => $existingUser ? $request->password : $tempPassword,
            'division_id'                        => $request->division_id,
            'district_id'                        => $request->district_id,
            'thana_id'                           => $request->thana_id,
            'address'                            => $request->address,
            'pay_cash_in'                        => $request->payment === 'cash' ? 'cash' : null,
            'pay_online'                         => $request->payment === 'online'
                                                    ? (($request->pay_method ?? 'Online') . ' | TRX: ' . ($request->trx ?? ''))
                                                    : null,
            'monthly_amount'                     => $totalAmount,
            'development_fee'                    => $devFee,
            'check_in'                           => $request->check_in,
            'check_out'                          => null,
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
        $imageName = null;
        if ($request->hasFile('image_file')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image_file')->getClientOriginalExtension();
            $request->file('image_file')->move(public_path('bookingsimage'), $imageName);
        }
        $mailData = [
            'full_name'        => $request->full_name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'user_type'        => $request->user_type,
            'institution_name' => $request->institution_name,
            'education_level'  => $request->education_level,
            'education_class'  => $request->education_class,
            'father_name'      => $request->father_name,
            'mother_name'      => $request->mother_name,
            'father_nid'       => $request->father_nid,
            'mother_nid'       => $request->mother_nid,
            'workplace_name'   => $request->workplace_name,
            'nid'              => $request->nid,
            'create_at'        => $bookingDateTime,
            'floor_name'       => implode(', ', array_unique(array_filter($floorNames))),
            'room_json'        => $roomJsonData,  
            'total_amount'     => $totalAmount,
            'division_name'    => $divisionName,
            'district_name'    => $districtName,
            'thana_name'       => $thanaName,
            'payment_type'     => $request->payment,
            'pay_method'       => $request->pay_method,
            'trx'              => $request->trx,
            'image_file'       => $imageName,
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

        $user = \App\Models\User::whereRaw(
            "RIGHT(REPLACE(REPLACE(phone, '+', ''), ' ', ''), 11) = ?",
            [$last11]
        )->first();

        if (!$guest && !$user) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'            => true,
            'has_account'      => (bool) $user,
            'full_name'        => $guest ? $guest->full_name : ($user ? $user->name : ''),
            'user_type'        => $guest ? ($guest->user_type ?? 'student') : 'student',
            'institution_name' => $guest ? $guest->institution_name : null,
            'education_level'  => $guest ? $guest->education_level : null,
            'education_class'  => $guest ? $guest->education_class : null,
            'workplace_name'   => $guest ? $guest->workplace_name : null,
            'email'            => $guest ? $guest->email : ($user ? $user->email : null),
            'nid'              => $guest ? $guest->nid : null,
            'mother_nid'       => $guest ? $guest->mother_nid : null,
            'father_nid'       => $guest ? $guest->father_nid : null,
            'father_name'      => $guest ? $guest->father_name : null,
            'mother_name'      => $guest ? $guest->mother_name : null,
            'division_id'      => $guest ? $guest->division_id : null,
            'district_id'      => $guest ? $guest->district_id : null,
            'thana_id'         => $guest ? $guest->thana_id : null,
            'address'          => $guest && $guest->address ? $guest->address : ($user ? $user->address : null),
            'image_url'        => ($guest && $guest->image) ? asset('bookingsimage/' . $guest->image) : ($user && $user->user_image ? asset('storage/user/' . $user->user_image) : null),
            'image_name'       => $guest ? $guest->image : ($user ? $user->user_image : null),
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

        $query = RoomBookingHistory::with(['division', 'district', 'thana'])->where('status', 0);

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
                'father_phone',
                'mother_phone',
                'room_number',
                'monthly_amount',
                'roomprice',
                'advance_fee',
                'development_fee',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'user_type',
                'institution_name',
                'education_level',
                'education_class',
                'workplace_name',
                'address',
            ]), [
                'development_fee' => (float) ($row->development_fee ?? 0),
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
                'father_phone',
                'mother_phone',
                'room_number',
                'monthly_amount',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'user_type',
                'institution_name',
                'education_level',
                'education_class',
                'workplace_name',
                'address',
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
    
    public function releaseManagerIndex()
    {
        return view('backend.room.release');
    }

    public function getActiveBookings(Request $request)
    {
        $perPage       = (int) $request->get('per_page', 10);
        $page          = (int) $request->get('page', 1);
        $search        = trim($request->get('search', ''));
        $filter        = $request->get('filter', 'all'); // all, staying, leaving

        $query = RoomBookingHistory::where('status', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($filter === 'staying') {
            $query->where('will_leave', 0);
        } elseif ($filter === 'leaving') {
            $query->where('will_leave', 1);
        }

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            $totalAdvance = collect($items)->sum(function($item) {
                return (float) ($item['advance_price'] ?? 0);
            });

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
                'monthly_amount',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
                'user_type',
                'will_leave',
                'notice_date'
            ]), [
                'group_key'             => 'booking_' . $row->id,
                'floornumber'           => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'            => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'                 => $c->pluck('price')->filter()->implode(', '),
                'room_items'            => $items,
                'total_advance_deposit' => $totalAdvance,
                'notice_date_formatted' => $row->notice_date ? \Carbon\Carbon::parse($row->notice_date)->format('Y-m-d') : null,
                'notice_days_elapsed'   => $row->notice_date ? (int) \Carbon\Carbon::parse($row->notice_date)->diffInDays(now()) : 0,
                'is_notice_fulfilled'   => $row->will_leave == 1 && $row->notice_date && \Carbon\Carbon::parse($row->notice_date)->diffInDays(now()) >= 60,
                'fine_amount'           => ($row->will_leave == 1 && $row->notice_date && \Carbon\Carbon::parse($row->notice_date)->diffInDays(now()) >= 60) ? 0 : ((float) ($row->monthly_amount ?? 0) * 2)
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

    public function scheduleLeave($id)
    {
        try {
            $booking = RoomBookingHistory::findOrFail($id);
            $booking->update([
                'will_leave'  => 1,
                'notice_date' => now()
            ]);
            return response()->json(['success' => true, 'message' => 'Successfully recorded 2 months leave notice.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelLeave($id)
    {
        try {
            $booking = RoomBookingHistory::findOrFail($id);
            $booking->update([
                'will_leave'  => 0,
                'notice_date' => null
            ]);
            return response()->json(['success' => true, 'message' => 'Leave notice cancelled successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function instantRelease($id)
    {
        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::findOrFail($id);

            $noticeDaysElapsed = $booking->notice_date ? (int) \Carbon\Carbon::parse($booking->notice_date)->diffInDays(now()) : 0;
            $isNoticeFulfilled = $booking->will_leave == 1 && $noticeDaysElapsed >= 60;

            $rawItems = is_string($booking->floor_number_room_number_roomprice)
                ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                : ($booking->floor_number_room_number_roomprice ?? []);

            // Update advance_price in floor_number_room_number_roomprice JSON array:
            // 1) If notice was fulfilled: Advance is refunded to resident upon checkout, so set advance_price = 0.
            // 2) If notice was NOT fulfilled: Penalty (2 months rent) is deducted from advance_price.
            if ($isNoticeFulfilled) {
                $updatedItems = array_map(function ($item) {
                    if (!isset($item['original_advance_price'])) {
                        $item['original_advance_price'] = (float)($item['advance_price'] ?? 0);
                    }
                    $item['advance_price'] = 0;
                    return $item;
                }, $rawItems);
                $booking->floor_number_room_number_roomprice = $updatedItems;
            } else {
                $penaltyAmount = (float) ($booking->monthly_amount ?? 0) * 2;
                $remainingPenaltyToDeduct = $penaltyAmount;

                $updatedItems = array_map(function ($item) use (&$remainingPenaltyToDeduct) {
                    $adv = (float) ($item['advance_price'] ?? 0);
                    if (!isset($item['original_advance_price'])) {
                        $item['original_advance_price'] = $adv;
                    }
                    if ($adv > 0 && $remainingPenaltyToDeduct > 0) {
                        if ($adv >= $remainingPenaltyToDeduct) {
                            $item['advance_price'] = $adv - $remainingPenaltyToDeduct;
                            $remainingPenaltyToDeduct = 0;
                        } else {
                            $remainingPenaltyToDeduct -= $adv;
                            $item['advance_price'] = 0;
                        }
                    }
                    return $item;
                }, $rawItems);

                $booking->floor_number_room_number_roomprice = $updatedItems;
            }

            foreach ($rawItems as $item) {
                $rn = $item['roomnumber'] ?? $item['room_number'] ?? null;
                if ($rn) {
                    $parts = explode('-', $rn, 2);
                    $roomNo = $parts[0] ?? '';
                    $seatNo = $parts[1] ?? '';

                    $room = Room::where('room_no', $roomNo)->first();
                    if ($room) {
                        RoomSeat::where('room_id', $room->id)->where('seat_no', $seatNo)->update(['status' => 0]);
                        Room::syncRoomStatus($room->id);
                    } else {
                        Room::where('room_no', $rn)->update(['status' => 0]);
                    }
                }
            }

            $booking->status = 1;
            $booking->today_check_out = now();
            $booking->check_out = now()->toDateString();
            $booking->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Resident checked out and seat released successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function releaseHistoryIndex()
    {
        return view('backend.room.release_history');
    }

    public function getReleaseHistory(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));

        // status = 1 represents checked-out residents
        $query = RoomBookingHistory::where('status', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $mapped = $query->orderByDesc('today_check_out')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            // Dynamically calculate the outstanding due from monthly payments
            $dueAmount = \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $row->id)->sum('due_amount');

            $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
            $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
            $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
            $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;
            
            $totalAdv = $c->sum(function($i) { 
                return (float)($i['original_advance_price'] ?? $i['advance_price'] ?? 0); 
            });
            if ($totalAdv == 0) {
                $totalAdv = (float) ($row->monthly_amount ?? 0) * 2;
            }

            return array_merge($row->only([
                'id',
                'image',
                'full_name',
                'father_name',
                'mother_name',
                'email',
                'phone',
                'nid',
                'room_number',
                'monthly_amount',
                'check_in',
                'check_out',
                'today_check_out',
                'status',
                'created_at',
                'user_type'
            ]), [
                'floornumber'           => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'            => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'                 => $c->pluck('price')->filter()->implode(', '),
                'room_items'            => $items,
                'due_amount'            => (float) $dueAmount,
                'will_leave'            => (int) $row->will_leave,
                'notice_date'           => $row->notice_date,
                'notice_days_elapsed'   => $noticeDaysElapsed,
                'is_notice_fulfilled'   => $isNoticeFulfilled,
                'total_advance_deposit' => $totalAdv,
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

    public function changeManagerIndex()
    {
        return view('backend.room.change_room');
    }

    public function getAvailableSeatsTree()
    {
        $floors = Floor::with(['rooms' => function ($rq) {
            $rq->with(['seats' => function ($sq) {
                $sq->where('status', 0);
            }]);
        }])->get()->map(function ($floor) {
            $rooms = $floor->rooms->map(function ($room) {
                $availableSeats = $room->seats->map(function ($seat) {
                    return [
                        'id'            => $seat->id,
                        'seat_no'       => $seat->seat_no,
                        'price'         => $seat->price,
                        'advance_price' => $seat->advance_price,
                    ];
                })->values();

                return [
                    'id'              => $room->id,
                    'room_no'         => $room->room_no,
                    'available_seats' => $availableSeats,
                ];
            })->filter(function ($room) {
                return count($room['available_seats']) > 0;
            })->values();

            return [
                'id'         => $floor->id,
                'name'       => $floor->name,
                'rooms_list' => $rooms,
            ];
        })->filter(function ($floor) {
            return count($floor['rooms_list']) > 0;
        })->values();

        return response()->json(['status' => 'success', 'data' => $floors]);
    }

    public function changeRoomSeat(Request $request, $id)
    {
        $request->validate([
            'new_seat_id'    => 'required|numeric',
            'fee_amount'     => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payment_status' => 'nullable|string',
            'remarks'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::where('id', $id)->where('status', 0)->firstOrFail();

            $newSeat = RoomSeat::with('room.floor')->where('id', $request->new_seat_id)->lockForUpdate()->firstOrFail();

            if ((int) $newSeat->status === 1) {
                return response()->json(['success' => false, 'message' => 'The selected seat is already booked.'], 422);
            }

            $items = is_string($booking->floor_number_room_number_roomprice)
                ? (json_decode($booking->floor_number_room_number_roomprice, true) ?? [])
                : ($booking->floor_number_room_number_roomprice ?? []);

            $oldFloorName = '';
            $oldRoomSeatString = '';
            $oldMonthlyAmount = (float) ($booking->monthly_amount ?? 0);

            if (!empty($items) && is_array($items)) {
                $oldFloorName = $items[0]['floornumber'] ?? '';
                $oldRoomSeatString = $items[0]['roomnumber'] ?? '';
            }

            // 1. Release old seat(s)
            foreach ($items as $item) {
                $rawRoomNo = $item['roomnumber'] ?? '';
                $parts = explode('-', $rawRoomNo, 2);
                $oldRoomNo = $parts[0] ?? '';
                $oldSeatNo = $parts[1] ?? '';

                if ($oldRoomNo && $oldSeatNo) {
                    $oldRoom = Room::where('room_no', $oldRoomNo)->first();
                    if ($oldRoom) {
                        $oldSeat = RoomSeat::where('room_id', $oldRoom->id)->where('seat_no', $oldSeatNo)->first();
                        if ($oldSeat) {
                            $oldSeat->update(['status' => 0]);
                            Room::syncRoomStatus($oldRoom->id);
                        }
                    }
                }
            }

            // 2. Book new seat
            $newSeat->update(['status' => 1]);
            $newRoom = $newSeat->room;
            $newFloor = $newRoom->floor;
            Room::syncRoomStatus($newRoom->id);

            $newRoomNumberString = $newRoom->room_no . '-' . $newSeat->seat_no;
            $newPrice = (float) ($newSeat->price ?? $newRoom->price ?? 0);

            $newItems = [
                [
                    'floornumber'   => (string) ($newFloor->name ?? ''),
                    'roomnumber'    => (string) $newRoomNumberString,
                    'price'         => $newPrice,
                    'advance_price' => (float) ($newSeat->advance_price ?? 0),
                ]
            ];

            $booking->update([
                'floor_number_room_number_roomprice' => $newItems,
                'monthly_amount'                     => $newPrice,
            ]);

            // 3. Record or Update Room Change History & Fee per resident
            $feeAmount = $request->filled('fee_amount') ? (float) $request->fee_amount : 500.00;
            $paymentMethod = $request->input('payment_method', 'Cash');
            $paymentStatus = $request->input('payment_status', 'paid');
            $remarks = $request->input('remarks', '');

            $existingHistory = RoomChangeHistory::where('room_booking_history_id', $booking->id)->first();

            if ($existingHistory) {
                $existingHistory->update([
                    'resident_name'      => $booking->full_name ?? $existingHistory->resident_name,
                    'phone'              => $booking->phone ?? $existingHistory->phone,
                    'old_floor'          => $oldFloorName,
                    'old_room_seat'      => $oldRoomSeatString,
                    'new_floor'          => (string) ($newFloor->name ?? ''),
                    'new_room_seat'      => (string) $newRoomNumberString,
                    'old_monthly_amount' => $oldMonthlyAmount,
                    'new_monthly_amount' => $newPrice,
                    'fee_amount'         => (float)$existingHistory->fee_amount + $feeAmount,
                    'payment_method'     => $paymentMethod,
                    'payment_status'     => $paymentStatus,
                    'remarks'            => $remarks,
                    'changed_by'         => Auth::id() ?? null,
                ]);
            } else {
                RoomChangeHistory::create([
                    'room_booking_history_id' => $booking->id,
                    'resident_name'           => $booking->full_name ?? '',
                    'phone'                   => $booking->phone ?? '',
                    'old_floor'               => $oldFloorName,
                    'old_room_seat'           => $oldRoomSeatString,
                    'new_floor'               => (string) ($newFloor->name ?? ''),
                    'new_room_seat'           => (string) $newRoomNumberString,
                    'old_monthly_amount'      => $oldMonthlyAmount,
                    'new_monthly_amount'      => $newPrice,
                    'fee_amount'              => $feeAmount,
                    'payment_method'          => $paymentMethod,
                    'payment_status'          => $paymentStatus,
                    'remarks'                 => $remarks,
                    'changed_by'              => Auth::id() ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room & Seat changed successfully! Change Fee (৳ ' . number_format($feeAmount) . ') recorded.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function roomChangeHistories(Request $request)
    {
        // Group by resident to ensure only 1 unique row per resident showing their latest change
        $latestIds = RoomChangeHistory::select(DB::raw('MAX(id) as max_id'))
            ->groupBy(DB::raw('COALESCE(room_booking_history_id, resident_name)'))
            ->pluck('max_id');

        $query = RoomChangeHistory::whereIn('id', $latestIds)->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('resident_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('old_room_seat', 'like', "%{$s}%")
                  ->orWhere('new_room_seat', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $histories = $query->paginate($perPage);

        return response()->json($histories);
    }

    public function devFeeIndex()
    {
        return view('backend.room.development_fee');
    }

    public function getDevFeeList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));
        $filter  = $request->get('filter', 'all');

        $query = RoomBookingHistory::where('status', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($filter === 'paid') {
            $query->where('development_fee', '>', 0);
        } elseif ($filter === 'unpaid') {
            $query->where(function ($q) {
                $q->whereNull('development_fee')->orWhere('development_fee', 0);
            });
        }

        $appSettings = \App\Services\SettingService::getSettingContentBySlug('app_setting');
        $defaultDevFee = (float) ($appSettings['development_fee'] ?? 3000);

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) use ($defaultDevFee) {
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
                'father_phone',
                'mother_phone',
                'room_number',
                'monthly_amount',
                'development_fee',
                'check_in',
                'check_out',
                'created_at',
                'user_type'
            ]), [
                'floornumber'     => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'      => $c->pluck('roomnumber')->filter()->implode(', '),
                'is_paid'         => (float) $row->development_fee > 0,
                'default_fee'     => $defaultDevFee,
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

    public function payDevFee(Request $request, $id)
    {
        try {
            $booking = RoomBookingHistory::findOrFail($id);

            $appSettings = \App\Services\SettingService::getSettingContentBySlug('app_setting');
            $feeAmount = (float) ($request->get('amount') ?: ($appSettings['development_fee'] ?? 3000));

            $booking->update([
                'development_fee' => $feeAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Development Fee (৳ ' . number_format($feeAmount) . ') recorded successfully!',
                'data'    => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function advanceFeeIndex()
    {
        return view('backend.room.advance_fee');
    }

    public function getAdvanceFeeList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));
        $filter  = $request->get('filter', 'all');

        $query = RoomBookingHistory::where('status', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($filter === 'paid') {
            $query->where('advance_fee', '>', 0);
        } elseif ($filter === 'unpaid') {
            $query->where(function ($q) {
                $q->whereNull('advance_fee')->orWhere('advance_fee', 0);
            });
        }

        $mapped = $query->orderByDesc('id')->get()->map(function ($row) {
            $items = is_string($row->floor_number_room_number_roomprice)
                ? (json_decode($row->floor_number_room_number_roomprice, true) ?? [])
                : ($row->floor_number_room_number_roomprice ?? []);

            $c = collect($items);

            $sumAdvancePrice = $c->sum(function ($item) {
                return (float) ($item['original_advance_price'] ?? $item['advance_price'] ?? 0);
            });

            $advanceFeeAmount = $sumAdvancePrice > 0
                ? $sumAdvancePrice
                : ((float) $row->advance_fee > 0 ? (float) $row->advance_fee : ((float) $row->monthly_amount ?: 0));

            return array_merge($row->only([
                'id',
                'image',
                'full_name',
                'father_name',
                'mother_name',
                'email',
                'phone',
                'father_phone',
                'mother_phone',
                'room_number',
                'monthly_amount',
                'development_fee',
                'check_in',
                'check_out',
                'created_at',
                'user_type'
            ]), [
                'advance_fee' => $advanceFeeAmount,
                'floornumber' => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'  => $c->pluck('roomnumber')->filter()->implode(', '),
                'is_paid'     => true,
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

    public function payAdvanceFee(Request $request, $id)
    {
        try {
            $booking = RoomBookingHistory::findOrFail($id);

            $feeAmount = (float) $request->get('amount', 0);

            $booking->update([
                'advance_fee' => $feeAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Advance Fee (৳ ' . number_format($feeAmount) . ') recorded successfully!',
                'data'    => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function checkPendingBookings(Request $request)
    {
        $lastId = (int) $request->query('last_id', 0);

        // Fetch ONLY unseen/unread bookings (is_seen = 0) that are active (status = 0)
        $unseenQuery = RoomBookingHistory::where('status', 0)->where('is_seen', 0);
        $pendingCount = (clone $unseenQuery)->count();

        $latestBookings = $unseenQuery->latest('id')
            ->take(10)
            ->get()
            ->map(function ($b) {
                return [
                    'id'         => $b->id,
                    'full_name'  => $b->full_name ?? 'Guest',
                    'phone'      => $b->phone ?? 'N/A',
                    'time_ago'   => $b->created_at ? $b->created_at->diffForHumans() : 'Recently',
                    'initial'    => strtoupper(substr($b->full_name ?? 'G', 0, 1)),
                    'view_url'   => route('bookings.mark-seen', $b->id),
                ];
            });

        $maxBooking = RoomBookingHistory::latest('id')->first();
        $latestId = $maxBooking ? $maxBooking->id : 0;

        $hasNew = false;
        if ($lastId > 0 && $latestId > $lastId) {
            $hasNew = true;
        }

        return response()->json([
            'count'     => $pendingCount,
            'latest_id' => $latestId,
            'has_new'   => $hasNew,
            'bookings'  => $latestBookings,
        ]);
    }

    public function markBookingSeen($id)
    {
        $booking = RoomBookingHistory::find($id);

        if ($booking) {
            $booking->update(['is_seen' => 1]);
            $searchTerm = !empty($booking->phone) ? $booking->phone : $booking->full_name;
            return redirect(url('/room-booking-history') . '?search=' . urlencode($searchTerm));
        }

        return redirect(url('/room-booking-history'));
    }
}