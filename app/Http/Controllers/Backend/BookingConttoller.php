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
            ->whereDate('check_in', today());

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

            return array_merge($row->only(['id','image','full_name','email','phone','nid','room_number','monthly_amount','check_in','check_out','status','created_at','pay_cash_in','pay_online']), [
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

    $webSetting = \App\Services\SettingService::getSettingContentBySlug('web_setting');
    $adminEmails = array_values(array_filter(array_unique([
        'mr2798492@gmail.com',
        $webSetting['email'] ?? null,
        config('mail.from.address')
    ])));
    $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
    $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
    $existingUser = null;
    if (!empty($cleanPhone10)) {
        $existingUser = User::where(function ($q) use ($request, $cleanPhone, $cleanPhone10) {
            $q->where('phone', $request->phone)
              ->orWhere('phone', $cleanPhone)
              ->orWhereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 10) = ?", [$cleanPhone10]);
        })->first();
    }
    if (!$existingUser && $request->filled('email')) {
        $userByEmail = User::where('email', $request->email)->first();
        if ($userByEmail) {
            $uClean = preg_replace('/[^0-9]/', '', $userByEmail->phone ?? '');
            $uClean10 = strlen($uClean) >= 10 ? substr($uClean, -10) : $uClean;
            if (empty($uClean10) || $uClean10 === $cleanPhone10) {
                $existingUser = $userByEmail;
            }
        }
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
            $seat = RoomSeat::with(['room.floor'])
                ->whereHas('room', function ($q) use ($roomNumber) {
                    $q->whereRaw("CONCAT(room_no, '-', seat_no) = ?", [$roomNumber]);
                })
                ->lockForUpdate()
                ->first();

            $room = $seat ? $seat->room : null;

            if (!$seat) {
                $lastDashPos = strrpos($roomNumber, '-');
                if ($lastDashPos !== false) {
                    $rNo = substr($roomNumber, 0, $lastDashPos);
                    $sNo = substr($roomNumber, $lastDashPos + 1);

                    $roomCandidate = Room::with('floor')->where('room_no', $rNo)->first();
                    if ($roomCandidate) {
                        $seatCandidate = RoomSeat::where('room_id', $roomCandidate->id)
                            ->where('seat_no', $sNo)
                            ->lockForUpdate()
                            ->first();
                        if ($seatCandidate) {
                            $seat = $seatCandidate;
                            $room = $roomCandidate;
                        }
                    }
                }
            }

            if (!$seat) {
                $parts = explode('-', $roomNumber, 2);
                $rNo = $parts[0] ?? '';
                $sNo = $parts[1] ?? '';

                $roomCandidate = Room::with('floor')->where('room_no', $rNo)->first();
                if ($roomCandidate) {
                    $seatCandidate = RoomSeat::where('room_id', $roomCandidate->id)
                        ->where('seat_no', $sNo)
                        ->lockForUpdate()
                        ->first();
                    if ($seatCandidate) {
                        $seat = $seatCandidate;
                        $room = $roomCandidate;
                    }
                }
            }

            if (!$room || !$seat) {
                throw new \Exception("Room not found: {$roomNumber}");
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

            $cleanRoomStr = RoomBookingHistory::parseRoomSeatLabel($roomNumber, $room->room_type ?? null, $seat->id ?? null);
            if (!$cleanRoomStr || $cleanRoomStr === '-') {
                $cleanRoomStr = (string) $roomNumber;
            }

            $roomJsonData[] = [
                'floornumber'   => (string) $floorName,
                'roomnumber'    => $cleanRoomStr,
                'price'         => $singleRoomPrice,
                'room_type'     => (string) ($room->room_type ?? ''),
                'advance_price' => (float) ($seat->advance_price ?? 0),
                'room_id'       => $room->id,
                'seat_id'       => $seat->id,
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

        $userTypeRole = $request->filled('user_type') ? ucfirst(str_replace('_', ' ', $request->user_type)) : 'Student';
        $role = Role::firstOrCreate([
            'name'       => $userTypeRole,
            'guard_name' => 'web'
        ]);

        if ($existingUser) {
            $existingUser->update([
                'name'       => $request->full_name ?: $existingUser->name,
                'email'      => $request->email ?: $existingUser->email,
                'phone'      => $request->phone ?: $existingUser->phone,
                'address'    => $request->address ?: $existingUser->address,
                'user_image' => $imagePath ?: $existingUser->user_image,
                'status'     => 1,
            ]);
            $existingUser->syncRoles([$role]);
            $user = $existingUser;
        } else {
            $emailToSave = $request->filled('email') ? $request->email : null;
            if ($emailToSave && User::where('email', $emailToSave)->where('status', 1)->exists()) {
                $emailToSave = null;
            }
            $user = User::create([
                'name'          => $request->full_name,
                'email'         => $emailToSave,
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
        $devFee = $devFeeActive ? (float) ($request->development_fee ?? $appSettings['development_fee'] ?? 0.00) : 0.00;

        RoomBookingHistory::create([
            'image'                              => $imagePath,
            'floor_number_room_number_roomprice' => $roomJsonData,
            'full_name'                          => $request->full_name,
            'user_type'                          => $request->user_type ?? 'student',
            'father_name'                        => $request->father_name,
            'mother_name'                        => $request->mother_name,
            'father_nid'                         => $request->father_nid,
            'mother_nid'                         => $request->mother_nid,
            'father_phone'                       => $request->father_phone,
            'mother_phone'                       => $request->mother_phone,
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

        $mailData = [
            'full_name'        => $request->full_name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'blood_group'      => $request->blood_group,
            'temp_password'    => $tempPassword,
            'password'         => $tempPassword,
            'user_type'        => $request->user_type ?? 'student',
            'institution_name' => $request->institution_name,
            'education_level'  => $request->education_level,
            'education_class'  => $request->education_class,
            'nid'              => $request->nid ?: $request->mother_nid,
            'mother_nid'       => $request->mother_nid,
            'father_nid'       => $request->father_nid,
            'father_name'      => $request->father_name,
            'father_phone'     => $request->father_phone,
            'mother_name'      => $request->mother_name,
            'mother_phone'     => $request->mother_phone,
            'workplace_name'   => $request->workplace_name,
            'floor_name'       => implode(', ', array_unique(array_filter($floorNames))),
            'room_numbers'     => implode(', ', $selectedRoomNumbers),
            'room_acstatus'    => implode(', ', $selectedAcStatus),
            'check_in'         => $request->check_in,
            'check_out'        => $request->check_out,
            'total_amount'     => $totalAmount,
            'monthly_amount'   => $totalAmount,
            'advance_total'    => $advanceTotal,
            'dev_fee'          => $devFee,
            'development_fee'  => $devFee,
            'booking_total'    => $advanceTotal + $devFee,
            'division_name'    => $divisionName,
            'district_name'    => $districtName,
            'thana_name'       => $thanaName,
            'payment_type'     => $request->payment,
            'pay_method'       => $request->pay_method,
            'trx'              => $request->trx,
            'image_file'       => $imagePath,
            'create_at'        => $bookingDateTime,
            'room_json'        => $roomJsonData,
        ];

        try {
            foreach ($adminEmails as $aEmail) {
                if (filter_var($aEmail, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($aEmail)->send(new RoomBookingMail($mailData));
                }
            }
            $guestEmail = trim($request->email ?? '');
            if (!empty($guestEmail) && filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($guestEmail)->send(new RoomBookingMail($mailData));
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
                'monthly_amount',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
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
                'monthly_amount',
                'check_in',
                'check_out',
                'status',
                'created_at',
                'pay_cash_in',
                'pay_online',
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
