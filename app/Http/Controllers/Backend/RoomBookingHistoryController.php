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

        $items = array_map(function ($item) {
            if (is_array($item)) {
                $seatModel = null;
                if (!empty($item['seat_id'])) {
                    $seatModel = \App\Models\Backend\RoomSeat::with('room')->find($item['seat_id']);
                }
                if (!$seatModel && !empty($item['roomnumber'])) {
                    $raw = trim($item['roomnumber']);
                    $rNo = null; $sNo = null;
                    if (preg_match('/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)(?:[\-_ ]*(\d+))?$/i', $raw, $m)) {
                        $rNo = $m[1]; $sNo = $m[3] ?? null;
                    } elseif (preg_match('/^([A-Za-z]+)[\-_](\d+)(?:[\-_](\d+))?$/i', $raw, $m)) {
                        $rNo = $m[2]; $sNo = $m[3] ?? null;
                    } elseif (preg_match('/^(\d+)[\-_]([A-Za-z]+)(?:[\-_](\d+))?$/i', $raw, $m)) {
                        $rNo = $m[1]; $sNo = $m[3] ?? null;
                    }
                    if ($rNo) {
                        $roomCand = \App\Models\Backend\Room::where('room_no', $rNo)->first();
                        if ($roomCand) {
                            if ($sNo) {
                                $seatModel = \App\Models\Backend\RoomSeat::where('room_id', $roomCand->id)->where('seat_no', $sNo)->first();
                            } else {
                                $seatModel = \App\Models\Backend\RoomSeat::where('room_id', $roomCand->id)->first();
                            }
                        }
                    }
                }
                if ($seatModel && $seatModel->room) {
                    if (empty($item['room_type'])) {
                        $item['room_type'] = $seatModel->room->room_type;
                    }
                    if (empty($item['seat_no']) && !empty($seatModel->seat_no)) {
                        $item['seat_no'] = $seatModel->seat_no;
                    }
                }
            }
            return $item;
        }, $items);

        $c = collect($items);
        $cleanPhone = preg_replace('/[^0-9]/', '', $row->phone);
        $uObj = null;
        if (!empty($row->email)) {
            $uObj = \App\Models\User::where('email', $row->email)->first();
        }
        if (!$uObj && !empty($cleanPhone)) {
            $uObj = \App\Models\User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$cleanPhone])->first();
        }

        return array_merge($row->only([
            'id',
            'image',
            'full_name',
            'father_name',
            'mother_name',
            'email',
            'blood_group',
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
            'user_status'   => ($row->status == 1 && $uObj && (int) $uObj->status == 1) ? 1 : 0,
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
        'blood_group'      => 'nullable|string|max:20',
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
    $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
    $existingUser = null;
    if (!empty($cleanPhone10)) {
        $existingUser = User::where('status', 1)
            ->where(function ($q) use ($request, $cleanPhone, $cleanPhone10) {
                $q->where('phone', $request->phone)
                  ->orWhere('phone', $cleanPhone)
                  ->orWhereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 10) = ?", [$cleanPhone10]);
            })->first();
    }
    if (!$existingUser && $request->filled('email')) {
        $userByEmail = User::where('email', $request->email)
            ->where('status', 1)
            ->first();
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

        $singleRoomPrice    = (float) ($seat->price ?? 0);           
        $singleAdvancePrice = (float) ($seat->advance_price ?? 0);  
        $floorName          = $room->floor->name ?? '';

            // Note: Seat status is kept 0 until Admin approves the booking
            $selectedRoomNumbers[] = $room->room_no;
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
                'advance_price' => $singleAdvancePrice,
                'room_id'       => $room->id,
                'seat_id'       => $seat->id,
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
        $advanceTotal = 0;
        foreach ($roomJsonData as $rItem) {
            $advanceTotal += (float)($rItem['advance_price'] ?? 0);
        }

        $appSettings = \App\Services\SettingService::getSettingContentBySlug('app_setting');
        $devFeeActive = ($appSettings['development_fee_status'] ?? '1') == '1';
        $devFee = $request->filled('development_fee')
            ? (float)$request->development_fee
            : ($devFeeActive ? (float) ($appSettings['development_fee'] ?? 0) : 0.00);

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
            'blood_group'                        => $request->blood_group,
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
            'advance_fee'                        => $advanceTotal,
            'development_fee'                    => $devFee,
            'check_in'                           => $request->check_in,
            'check_out'                          => null,
            'status'                             => 0,
        ]);

        DB::commit();
        $divisionName    = optional(Division::find($request->division_id))->name ?? '-';
        $districtName    = optional(District::find($request->district_id))->name ?? '-';
        $thanaName       = optional(Thana::find($request->thana_id))->name ?? '-';
        $bookingDateTime = Carbon::now('Asia/Dhaka')->format('d/m/Y g:i A');
        $webSetting      = \App\Services\SettingService::getSettingContentBySlug('web_setting');
        $adminEmails     = array_values(array_filter(array_unique([
            'mr2798492@gmail.com',
            $webSetting['email'] ?? null,
            config('mail.from.address')
        ])));
        $imageName = null;
        if ($request->hasFile('image_file')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image_file')->getClientOriginalExtension();
            $request->file('image_file')->move(public_path('bookingsimage'), $imageName);
        }
        $mailData = [
            'full_name'        => $request->full_name,
            'email'            => $request->email,
            'blood_group'      => $request->blood_group,
            'phone'            => $request->phone,
            'temp_password'    => $tempPassword,
            'password'         => $tempPassword,
            'user_type'        => $request->user_type,
            'institution_name' => $request->institution_name,
            'education_level'  => $request->education_level,
            'education_class'  => $request->education_class,
            'father_name'      => $request->father_name,
            'father_phone'     => $request->father_phone,
            'mother_name'      => $request->mother_name,
            'mother_phone'     => $request->mother_phone,
            'father_nid'       => $request->father_nid,
            'mother_nid'       => $request->mother_nid,
            'workplace_name'   => $request->workplace_name,
            'nid'              => $request->nid,
            'create_at'        => $bookingDateTime,
            'floor_name'       => implode(', ', array_unique(array_filter($floorNames))),
            'room_json'        => $roomJsonData,
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
            'image_file'       => $imageName,
        ];

        try {
            // ১. অ্যাডমিনদের ইমেইলে বুকিং নোটিফিকেশন পাঠানো
            foreach ($adminEmails as $aEmail) {
                if (filter_var($aEmail, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($aEmail)->send(new RoomBookingMail($mailData));
                }
            }
            // ২. বুকিং প্রদানকারী ইউজারের ইমেইলে সরাসরি কনফার্মেশন ও লগইন বিবরণী পাঠানো
            $guestEmail = trim($request->email ?? '');
            if (!empty($guestEmail) && filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($guestEmail)->send(new RoomBookingMail($mailData));
            }
        } catch (\Throwable $mailError) {
            \Log::error('Room booking email failed: ' . $mailError->getMessage());
        }
            if ($request->ajax()) {
                return response()->json([
                    'status'          => true,
                    'booking_success' => true,
                    'message'         => 'আপনার বুকিং আবেদনটি সফলভাবে জমা হয়েছে। অ্যাডমিন কনফার্ম ও একটিভ (Active) করলে আপনি ড্যাশবোর্ডে লগইন করতে পারবেন।',
                    'redirect_url'    => null,
                ], 200);
        }
        return redirect('/login')->with('success', 'আপনার বুকিং আবেদনটি সফলভাবে জমা হয়েছে। অ্যাডমিন কনফার্ম ও একটিভ (Active) করলে আপনি ড্যাশবোর্ডে লগইন করতে পারবেন।');

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
            'blood_group'      => $guest ? $guest->blood_group : null,
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

        $query = RoomBookingHistory::with(['division', 'district', 'thana'])->whereNull('today_check_out');

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

            $items = array_map(function ($item) {
                if (is_array($item)) {
                    $seatModel = null;
                    if (!empty($item['seat_id'])) {
                        $seatModel = \App\Models\Backend\RoomSeat::with('room')->find($item['seat_id']);
                    }
                    if (!$seatModel && !empty($item['roomnumber'])) {
                        $raw = trim($item['roomnumber']);
                        $rNo = null; $sNo = null;
                        if (preg_match('/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)(?:[\-_ ]*(\d+))?$/i', $raw, $m)) {
                            $rNo = $m[1]; $sNo = $m[3] ?? null;
                        } elseif (preg_match('/^([A-Za-z]+)[\-_](\d+)(?:[\-_](\d+))?$/i', $raw, $m)) {
                            $rNo = $m[2]; $sNo = $m[3] ?? null;
                        } elseif (preg_match('/^(\d+)[\-_]([A-Za-z]+)(?:[\-_](\d+))?$/i', $raw, $m)) {
                            $rNo = $m[1]; $sNo = $m[3] ?? null;
                        }
                        if ($rNo) {
                            $roomCand = \App\Models\Backend\Room::where('room_no', $rNo)->first();
                            if ($roomCand) {
                                if ($sNo) {
                                    $seatModel = \App\Models\Backend\RoomSeat::where('room_id', $roomCand->id)->where('seat_no', $sNo)->first();
                                } else {
                                    $seatModel = \App\Models\Backend\RoomSeat::where('room_id', $roomCand->id)->first();
                                }
                            }
                        }
                    }
                    if ($seatModel && $seatModel->room) {
                        if (empty($item['room_type'])) {
                            $item['room_type'] = $seatModel->room->room_type;
                        }
                        if (empty($item['seat_no']) && !empty($seatModel->seat_no)) {
                            $item['seat_no'] = $seatModel->seat_no;
                        }
                    }
                }
                return $item;
            }, $items);

            $c = collect($items);

            $cleanPhone = preg_replace('/[^0-9]/', '', $row->phone);
            $uObj = null;
            if (!empty($row->email)) {
                $uObj = \App\Models\User::where('email', $row->email)->first();
            }
            if (!$uObj && !empty($cleanPhone)) {
                $uObj = \App\Models\User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$cleanPhone])->first();
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
                'mother_nid',
                'father_nid',
                'father_phone',
                'mother_phone',
                'blood_group',
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
                'user_status'   => $uObj ? (int) $uObj->status : (int) $row->status,
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
                'blood_group',
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

        $query = RoomBookingHistory::whereNull('today_check_out')
            ->where('status', 1);

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

            $totalAdvance = collect($items)->sum(function($item) use ($row) {
                $val = (float) ($item['original_advance_price'] ?? $item['advance_price'] ?? 0);
                if ($val > 0) {
                    return $val;
                }
                if (!empty($item['seat_id'])) {
                    $seat = \App\Models\Backend\RoomSeat::find($item['seat_id']);
                    if ($seat && (float)$seat->advance_price > 0) {
                        return (float)$seat->advance_price;
                    }
                }
                if (!empty($item['roomnumber'])) {
                    $rn = $item['roomnumber'];
                    $seat = \App\Models\Backend\RoomSeat::whereHas('room', function ($q) use ($rn) {
                        $q->whereRaw("CONCAT(room_no, '-', seat_no) = ?", [$rn]);
                    })->first();
                    if ($seat && (float)$seat->advance_price > 0) {
                        return (float)$seat->advance_price;
                    }
                }
                return (float) ($row->advance_fee ?? 0);
            });

            $cleanPhone = preg_replace('/[^0-9]/', '', $row->phone ?? '');
            $uObj = null;
            if (!empty($row->email)) {
                $uObj = \App\Models\User::where('email', $row->email)->first();
            }
            if (!$uObj && !empty($cleanPhone)) {
                $uClean10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
                $uObj = \App\Models\User::whereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 10) = ?", [$uClean10])->first();
            }

            $mealDepositTotal = 0.0;
            $mealCost = 0.0;
            $mealDepositBalance = 0.0;

            if ($uObj) {
                $mealBal = app(\App\Services\MealService::class)->getUserMealDepositBalance($uObj->id);
                $mealDepositTotal = (float)($mealBal['deposit_total'] ?? 0);
                $mealCost = (float)($mealBal['meal_cost'] ?? 0);
                $mealDepositBalance = (float)($mealBal['balance'] ?? 0);
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
                'user_id'               => $uObj ? $uObj->id : null,
                'group_key'             => 'booking_' . $row->id,
                'floornumber'           => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'            => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'                 => $c->pluck('price')->filter()->implode(', '),
                'room_items'            => $items,
                'total_advance_deposit' => $totalAdvance,
                'meal_deposit_total'    => $mealDepositTotal,
                'meal_cost'             => $mealCost,
                'meal_deposit_balance'  => $mealDepositBalance,
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
                    $seat = RoomSeat::whereHas('room', function ($q) use ($rn) {
                        $q->whereRaw("CONCAT(room_no, '-', seat_no) = ?", [$rn]);
                    })->first();

                    if ($seat) {
                        $seat->update(['status' => 0]);
                        Room::syncRoomStatus($seat->room_id);
                    } else {
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

        // Display only residents who have actually been checked-out / released
        $query = RoomBookingHistory::whereNotNull('today_check_out');

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

            // Dynamically calculate monthly payment due & emergency checkout penalty due
            $monthlyDue = (float) \App\Models\Backend\MonthlyPayment::where('room_booking_history_id', $row->id)->sum('due_amount');

            $noticeDate = $row->notice_date ? \Carbon\Carbon::parse($row->notice_date) : null;
            $checkoutDate = $row->today_check_out ? \Carbon\Carbon::parse($row->today_check_out) : ($row->check_out ? \Carbon\Carbon::parse($row->check_out) : now());
            $noticeDaysElapsed = $noticeDate ? (int) $noticeDate->diffInDays($checkoutDate) : 0;
            $isNoticeFulfilled = $row->will_leave == 1 && $noticeDaysElapsed >= 60;
            
            $totalAdv = $c->sum(function($item) use ($row) {
                $val = (float) ($item['original_advance_price'] ?? $item['advance_price'] ?? 0);
                if ($val > 0) return $val;
                if (!empty($item['seat_id'])) {
                    $seat = \App\Models\Backend\RoomSeat::find($item['seat_id']);
                    if ($seat && (float)$seat->advance_price > 0) return (float)$seat->advance_price;
                }
                return (float) ($row->advance_fee ?? 0);
            });

            $penaltyFine = $isNoticeFulfilled ? 0.0 : ((float) ($row->monthly_amount ?? 0) * 2);
            $uncoveredPenaltyDue = max(0.0, $penaltyFine - $totalAdv);
            $totalResidentDue = $monthlyDue + $uncoveredPenaltyDue;

            $cleanPhone = preg_replace('/[^0-9]/', '', $row->phone ?? '');
            $uObj = null;
            if (!empty($row->email)) {
                $uObj = \App\Models\User::where('email', $row->email)->first();
            }
            if (!$uObj && !empty($cleanPhone)) {
                $uClean10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
                $uObj = \App\Models\User::whereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 10) = ?", [$uClean10])->first();
            }

            $mealDepositTotal = 0.0;
            $mealCost = 0.0;
            $mealDepositBalance = 0.0;

            if ($uObj) {
                $mealBal = app(\App\Services\MealService::class)->getUserMealDepositBalance($uObj->id);
                $mealDepositTotal = (float)($mealBal['deposit_total'] ?? 0);
                $mealCost = (float)($mealBal['meal_cost'] ?? 0);
                $mealDepositBalance = (float)($mealBal['balance'] ?? 0);
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
                'user_id'               => $uObj ? $uObj->id : null,
                'floornumber'           => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'            => $c->pluck('roomnumber')->filter()->implode(', '),
                'price'                 => $c->pluck('price')->filter()->implode(', '),
                'room_items'            => $items,
                'due_amount'            => (float) $totalResidentDue,
                'monthly_due'           => (float) $monthlyDue,
                'penalty_due'           => (float) $uncoveredPenaltyDue,
                'will_leave'            => (int) $row->will_leave,
                'notice_date'           => $row->notice_date,
                'notice_days_elapsed'   => $noticeDaysElapsed,
                'is_notice_fulfilled'   => $isNoticeFulfilled,
                'total_advance_deposit' => $totalAdv,
                'meal_deposit_total'    => $mealDepositTotal,
                'meal_cost'             => $mealCost,
                'meal_deposit_balance'  => $mealDepositBalance,
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
        // 1. Fetch all bookings that are active or in processing (today_check_out is NULL)
        $activeBookings = RoomBookingHistory::whereNull('today_check_out')->get();

        $unavailableSeatIds = [];
        $unavailableSeatNames = [];

        foreach ($activeBookings as $b) {
            $items = is_string($b->floor_number_room_number_roomprice)
                ? (json_decode($b->floor_number_room_number_roomprice, true) ?? [])
                : ($b->floor_number_room_number_roomprice ?? []);

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (!empty($item['seat_id'])) {
                        $unavailableSeatIds[] = (int) $item['seat_id'];
                    }
                    if (!empty($item['roomnumber'])) {
                        $unavailableSeatNames[] = trim((string) $item['roomnumber']);
                    }
                    if (!empty($item['seat_no'])) {
                        $unavailableSeatNames[] = trim((string) $item['seat_no']);
                    }
                }
            }
        }
        $unavailableSeatIds = array_unique(array_filter($unavailableSeatIds));
        $unavailableSeatNames = array_unique(array_filter($unavailableSeatNames));

        $floors = Floor::with(['rooms.seats'])->get()->map(function ($floor) use ($unavailableSeatIds, $unavailableSeatNames) {
            $rooms = $floor->rooms->map(function ($room) use ($unavailableSeatIds, $unavailableSeatNames) {
                $availableSeats = $room->seats->filter(function ($seat) use ($room, $unavailableSeatIds, $unavailableSeatNames) {
                    // Seat must be status 0
                    if ((int) $seat->status === 1) {
                        return false;
                    }
                    // Seat id must not be in unavailable seat ids
                    if (in_array((int) $seat->id, $unavailableSeatIds, true)) {
                        return false;
                    }
                    // Seat full name or seat_no must not be in unavailable seat names
                    $fullSeatName = $room->room_no . '-' . $seat->seat_no;
                    if (in_array($fullSeatName, $unavailableSeatNames, true) || in_array($seat->seat_no, $unavailableSeatNames, true)) {
                        return false;
                    }
                    return true;
                })->map(function ($seat) {
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
            $booking = RoomBookingHistory::where('id', $id)->whereNull('today_check_out')->firstOrFail();

            $newSeat = RoomSeat::with('room.floor')->where('id', $request->new_seat_id)->lockForUpdate()->firstOrFail();

            $isOccupiedOrProcessing = RoomBookingHistory::whereNull('today_check_out')
                ->where('id', '!=', $booking->id)
                ->where(function($q) use ($newSeat) {
                    $seatString = $newSeat->room->room_no . '-' . $newSeat->seat_no;
                    $q->where('floor_number_room_number_roomprice', 'like', "%{$seatString}%")
                      ->orWhere('floor_number_room_number_roomprice', 'like', "%\"seat_id\":{$newSeat->id}%")
                      ->orWhere('floor_number_room_number_roomprice', 'like', "%\"seat_id\": \"{$newSeat->id}\"%");
                })->exists();

            if ((int) $newSeat->status === 1 || $isOccupiedOrProcessing) {
                return response()->json(['success' => false, 'message' => 'The selected seat is already booked or processing.'], 422);
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
                if ($rawRoomNo) {
                    $oldSeat = RoomSeat::whereHas('room', function ($q) use ($rawRoomNo) {
                        $q->whereRaw("CONCAT(room_no, '-', seat_no) = ?", [$rawRoomNo]);
                    })->first();

                    if ($oldSeat) {
                        $oldSeat->update(['status' => 0]);
                        Room::syncRoomStatus($oldSeat->room_id);
                    } else {
                        $parts = explode('-', $rawRoomNo, 2);
                        $oldRoomNo = $parts[0] ?? '';
                        $oldSeatNo = $parts[1] ?? '';

                        if ($oldRoomNo && $oldSeatNo) {
                            $oldRoom = Room::where('room_no', $oldRoomNo)->first();
                            if ($oldRoom) {
                                $s = RoomSeat::where('room_id', $oldRoom->id)->where('seat_no', $oldSeatNo)->first();
                                if ($s) {
                                    $s->update(['status' => 0]);
                                    Room::syncRoomStatus($oldRoom->id);
                                }
                            }
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
                    'room_id'       => $newRoom->id,
                    'seat_id'       => $newSeat->id,
                ]
            ];

            $booking->update([
                'floor_number_room_number_roomprice' => $newItems,
                'monthly_amount'                     => $newPrice,
            ]);

            // 3. Record NEW Room Change History & Fee for every change event
            $feeAmount = $request->filled('fee_amount') ? (float) $request->fee_amount : 500.00;
            $paymentMethod = $request->input('payment_method', 'Cash');
            $paymentStatus = $request->input('payment_status', 'paid');
            $remarks = $request->input('remarks', '');

            $history = RoomChangeHistory::create([
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

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'রুম ও সিট সফলভাবে পরিবর্তন করা হয়েছে!',
                'change_history' => $history,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Change Room Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function roomChangeHistories(Request $request)
    {
        $query = RoomChangeHistory::orderBy('id', 'desc');

        if ($request->filled('booking_id')) {
            $query->where('room_booking_history_id', $request->booking_id);
        }

        if ($request->filled('phone')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
            $query->where('phone', 'like', "%{$last10}%");
        }

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

    public function activateBookingAccount($id)
    {
        try {
            $booking = RoomBookingHistory::findOrFail($id);
            $booking->update(['status' => 1]);

            $rawPhone = $booking->phone;
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10Digits = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $user = null;
            if (!empty($booking->email)) {
                $user = User::where('email', $booking->email)->first();
            }
            if (!$user && !empty($last10Digits)) {
                $user = User::where('phone', 'like', "%{$last10Digits}")->first();
            }

            if ($user) {
                $user->update(['status' => 1]);
            }

            // Update associated booked room seat(s) to status 1 (Occupied/Disabled) upon Admin approval
            $roomsJson = $booking->floor_number_room_number_roomprice;
            $roomsArr  = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
            if (is_array($roomsArr)) {
                foreach ($roomsArr as $rItem) {
                    $seatId     = $rItem['seat_id'] ?? null;
                    $roomId     = $rItem['room_id'] ?? null;
                    $seatString = $rItem['roomnumber'] ?? ($rItem['seat_no'] ?? null);

                    $targetSeat = null;
                    if ($seatId) {
                        $targetSeat = RoomSeat::find($seatId);
                    }
                    if (!$targetSeat && $seatString) {
                        $parts = explode('-', $seatString, 2);
                        $rNo   = trim($parts[0] ?? '');
                        $sNo   = trim($parts[1] ?? $seatString);

                        $roomObj = Room::where('room_no', $rNo)->first();
                        if ($roomObj) {
                            $targetSeat = RoomSeat::where('room_id', $roomObj->id)
                                ->where(function($q) use ($sNo, $seatString) {
                                    $q->where('seat_no', $sNo)
                                      ->orWhere('seat_no', $seatString);
                                })->first();
                        }
                    }

                    if ($targetSeat) {
                        $targetSeat->update(['status' => 1]);
                        Room::syncRoomStatus($targetSeat->room_id);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'শিক্ষার্থীর অ্যাকাউন্ট ও রুম সীট সফলভাবে একটিভ (Active & Booked) করা হয়েছে!',
                'status'  => 1
            ]);
        } catch (\Throwable $e) {
            \Log::error("Activate booking account error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deactivateBookingAccount($id)
    {
        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::findOrFail($id);
            $booking->update(['status' => 0]);

            $rawPhone = $booking->phone;
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10Digits = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $user = null;
            if (!empty($booking->email)) {
                $user = User::where('email', $booking->email)->first();
            }
            if (!$user && !empty($last10Digits)) {
                $user = User::where('phone', 'like', "%{$last10Digits}")->first();
            }

            if ($user) {
                $user->update(['status' => 0]);
            }

            // Free up the associated room seat(s) (status 0) unless another approved booking is occupying it
            $roomsJson = $booking->floor_number_room_number_roomprice;
            $roomsArr  = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
            if (is_array($roomsArr)) {
                foreach ($roomsArr as $rItem) {
                    $seatId     = $rItem['seat_id'] ?? null;
                    $roomId     = $rItem['room_id'] ?? null;
                    $seatString = $rItem['roomnumber'] ?? ($rItem['seat_no'] ?? ($rItem['room_no'] ?? null));

                    $targetSeat = null;
                    if ($seatId) {
                        $targetSeat = RoomSeat::find($seatId);
                    }
                    if (!$targetSeat && $roomId) {
                        $parts = explode('-', (string)$seatString);
                        $sNo   = trim(end($parts));
                        $targetSeat = RoomSeat::where('room_id', $roomId)
                            ->where(function($q) use ($sNo, $seatString) {
                                $q->where('seat_no', $sNo)
                                  ->orWhere('seat_no', $seatString);
                            })->first();
                    }
                    if (!$targetSeat && $seatString) {
                        $parts = explode('-', (string)$seatString, 2);
                        $rNo   = trim($parts[0] ?? '');
                        $sNo   = trim($parts[1] ?? $seatString);

                        $roomObj = Room::where('room_no', $rNo)->first();
                        if ($roomObj) {
                            $targetSeat = RoomSeat::where('room_id', $roomObj->id)
                                ->where(function($q) use ($sNo, $seatString) {
                                    $q->where('seat_no', $sNo)
                                      ->orWhere('seat_no', $seatString);
                                })->first();
                        }
                    }

                    if ($targetSeat) {
                        $hasApprovedOther = RoomBookingHistory::where('id', '!=', $booking->id)
                            ->where('status', 1)
                            ->where('floor_number_room_number_roomprice', 'like', "%{$seatString}%")
                            ->exists();

                        if (!$hasApprovedOther) {
                            $targetSeat->update(['status' => 0]);
                            Room::syncRoomStatus($targetSeat->room_id);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'শিক্ষার্থীর অ্যাকাউন্ট সফলভাবে Deactive করা হয়েছে এবং সিট মুক্ত করা হয়েছে!',
                'status'  => 0
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Deactivate booking account error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyBooking($id)
    {
        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::findOrFail($id);

            // 1. Check associated user from users table
            $rawPhone = $booking->phone;
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10Digits = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $user = null;
            if (!empty($booking->email)) {
                $user = User::where('email', $booking->email)->first();
            }
            if (!$user && !empty($last10Digits)) {
                $user = User::where('phone', 'like', "%{$last10Digits}")->first();
            }

            if ($user) {
                // Deactivate user account while keeping original credentials intact
                $user->update(['status' => 0]);
            }

            // 2. Reset room seat status to 0 ONLY if NO OTHER approved booking (status = 1) is occupying that seat
            $roomsJson = $booking->floor_number_room_number_roomprice;
            $roomsArr  = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;

            if (is_array($roomsArr)) {
                foreach ($roomsArr as $rItem) {
                    $seatId     = $rItem['seat_id'] ?? null;
                    $roomId     = $rItem['room_id'] ?? null;
                    $seatString = $rItem['roomnumber'] ?? ($rItem['seat_no'] ?? ($rItem['room_no'] ?? null));

                    $targetSeat = null;

                    if ($seatId) {
                        $targetSeat = RoomSeat::find($seatId);
                    }

                    if (!$targetSeat && $roomId) {
                        $parts = explode('-', $seatString);
                        $sNo   = trim(end($parts));
                        $targetSeat = RoomSeat::where('room_id', $roomId)
                            ->where(function($q) use ($sNo, $seatString) {
                                $q->where('seat_no', $sNo)
                                  ->orWhere('seat_no', $seatString);
                            })->first();
                    }

                    if (!$targetSeat && $seatString) {
                        $parts = explode('-', $seatString, 2);
                        $rNo   = trim($parts[0] ?? '');
                        $sNo   = trim($parts[1] ?? $seatString);

                        $roomObj = Room::where('room_no', $rNo)->first();
                        if ($roomObj) {
                            $targetSeat = RoomSeat::where('room_id', $roomObj->id)
                                ->where(function($q) use ($sNo, $seatString) {
                                    $q->where('seat_no', $sNo)
                                      ->orWhere('seat_no', $seatString);
                                })->first();
                        }
                    }

                    if ($targetSeat) {
                        // Check if another APPROVED booking (status = 1) exists for this seat
                        $hasApprovedOther = RoomBookingHistory::where('id', '!=', $booking->id)
                            ->where('status', 1)
                            ->where('floor_number_room_number_roomprice', 'like', "%{$seatString}%")
                            ->exists();

                        if (!$hasApprovedOther) {
                            $targetSeat->update(['status' => 0]);
                            Room::syncRoomStatus($targetSeat->room_id);
                        }
                    }
                }
            }

            // 3. Soft-delete ONLY this single specific RoomBookingHistory record
            $booking->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'বুকিং রেকর্ডটি Deleted History-তে পাঠানো হয়েছে এবং সিট মুক্ত করা হয়েছে!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Destroy booking error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'মুছে ফেলতে ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
        }
    }

    public function deletedHistoryIndex()
    {
        return view('backend.room.deleted_history');
    }

    public function getDeletedBookings(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));

        $query = RoomBookingHistory::onlyTrashed()->with(['division', 'district', 'thana']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('floor_number_room_number_roomprice', 'like', "%{$search}%");
            });
        }

        $mapped = $query->orderByDesc('deleted_at')->get()->map(function ($row) {
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

            return array_merge($row->toArray(), [
                'advance_fee'    => $advanceFeeAmount,
                'floornumber'    => $c->pluck('floornumber')->filter()->unique()->implode(', '),
                'roomnumber'     => $c->pluck('roomnumber')->filter()->implode(', '),
                'room_items'     => $items,
                'division_name'  => $row->division ? $row->division->name : null,
                'district_name'  => $row->district ? $row->district->name : null,
                'thana_name'     => $row->thana ? $row->thana->name : null,
                'deleted_at_str' => $row->deleted_at ? $row->deleted_at->toDateTimeString() : null,
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

    public function restoreBooking(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::withTrashed()->findOrFail($id);

            // If admin selected a new room & seat
            if ($request->filled('seat_id')) {
                $newSeat = RoomSeat::with('room.floor')->where('id', $request->seat_id)->lockForUpdate()->firstOrFail();

                // Check seat availability
                $isOccupied = RoomBookingHistory::whereNull('today_check_out')
                    ->where(function($q) use ($newSeat) {
                        $seatString = $newSeat->room->room_no . '-' . $newSeat->seat_no;
                        $q->where('floor_number_room_number_roomprice', 'like', "%{$seatString}%")
                          ->orWhere('floor_number_room_number_roomprice', 'like', "%\"seat_id\":{$newSeat->id}%")
                          ->orWhere('floor_number_room_number_roomprice', 'like', "%\"seat_id\": \"{$newSeat->id}\"%");
                    })->exists();

                if ((int) $newSeat->status === 1 || $isOccupied) {
                    return response()->json(['success' => false, 'message' => 'নির্বাচিত সিটটি ইতিমধ্যে বুক করা রয়েছে! অন্য সিট বাছাই করুন।'], 422);
                }

                $newRoom = $newSeat->room;
                $newFloor = $newRoom->floor;
                $newRoomNumberString = $newRoom->room_no . '-' . $newSeat->seat_no;
                $newPrice = (float) ($newSeat->price ?? $newRoom->price ?? 0);
                $newAdvPrice = (float) ($newSeat->advance_price ?? 0);

                $newItems = [
                    [
                        'floornumber'   => (string) ($newFloor->name ?? ''),
                        'roomnumber'    => (string) $newRoomNumberString,
                        'price'         => $newPrice,
                        'advance_price' => $newAdvPrice,
                        'room_id'       => $newRoom->id,
                        'seat_id'       => $newSeat->id,
                    ]
                ];

                $booking->floor_number_room_number_roomprice = $newItems;
                $booking->monthly_amount = $newPrice;
                $booking->advance_fee = $newAdvPrice;

                // Mark new seat as occupied (status 1)
                $newSeat->update(['status' => 1]);
                Room::syncRoomStatus($newRoom->id);
            }

            if ($request->filled('development_fee')) {
                $booking->development_fee = (float) $request->development_fee;
            }

            $booking->status = 1; // Active
            $booking->today_check_out = null;
            $booking->save();
            $booking->restore();

            // Keep original email & password untouched, only reactivate status to 1
            $rawPhone = $booking->phone;
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10Digits = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $user = null;
            if (!empty($booking->email)) {
                $user = User::where('email', $booking->email)->first();
            }
            if (!$user && !empty($last10Digits)) {
                $user = User::where('phone', 'like', "%{$last10Digits}")->first();
            }

            if ($user) {
                // Keep original email & password untouched, only reactivate status to 1
                $user->update(['status' => 1]);
            } else if (!empty($booking->phone)) {
                try {
                    $userTypeRole = 'student';
                    if (!empty($booking->user_type)) {
                        $uTypeLower = strtolower(trim($booking->user_type));
                        if ($uTypeLower === 'working professional' || $uTypeLower === 'jobholder' || $uTypeLower === 'job_holder') {
                            $userTypeRole = 'working_professional';
                        }
                    }
                    $role = Role::firstOrCreate(['name' => $userTypeRole, 'guard_name' => 'web']);

                    $rawPass = !empty($booking->password) ? $booking->password : '123456';
                    $encPass = (strlen($rawPass) === 60 && str_starts_with($rawPass, '$2y$')) ? $rawPass : bcrypt($rawPass);

                    $newUser = User::create([
                        'name'          => $booking->full_name,
                        'email'         => !empty($booking->email) ? $booking->email : null,
                        'phone'         => $booking->phone,
                        'address'       => $booking->address,
                        'password'      => $encPass,
                        'temp_password' => $rawPass,
                        'user_image'    => $booking->image,
                        'status'        => 1,
                    ]);
                    $newUser->syncRoles([$role]);
                } catch (\Throwable $e) {
                    \Log::warning("User re-creation warning: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'গেস্ট বুকিং সফলভাবে Restore & Confirm করা হয়েছে!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Restore booking error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'রিস্টোর করতে সমস্যা হয়েছে: ' . $e->getMessage()], 500);
        }
    }

    public function forceDeleteBooking($id)
    {
        DB::beginTransaction();
        try {
            $booking = RoomBookingHistory::onlyTrashed()->findOrFail($id);
            $booking->forceDelete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'বুকিং রেকর্ডটি স্থায়ীভাবে (Permanently) মুছে ফেলা হয়েছে!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Force delete booking error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'স্থায়ীভাবে মুছতে ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
        }
    }

    public function bookingButtonIndex()
    {
        $appSettingModel = \App\Models\Backend\Setting::where('slug', 'app_setting')->first();
        $contents = $appSettingModel ? json_decode($appSettingModel->contents, true) : [];
        $status = $contents['booking_button_status'] ?? '1';

        return view('backend.booking_button.index', compact('status'));
    }

    public function updateBookingButtonStatus(Request $request)
    {
        $request->validate([
            'booking_button_status' => 'required|in:0,1',
        ]);

        $appSettingModel = \App\Models\Backend\Setting::where('slug', 'app_setting')->first();
        $contents = $appSettingModel ? json_decode($appSettingModel->contents, true) : [];
        $contents['booking_button_status'] = (string) $request->booking_button_status;

        if ($appSettingModel) {
            $appSettingModel->update([
                'contents' => json_encode($contents),
            ]);
        } else {
            \App\Models\Backend\Setting::create([
                'slug' => 'app_setting',
                'contents' => json_encode($contents),
            ]);
        }

        return redirect()->back()->with('success', 'Booking button status updated successfully!');
    }

    public function findGuestByPhone(Request $request)
    {
        $phone = $request->query('phone') ?? $request->input('phone');
        if (!$phone) {
            return response()->json(['found' => false, 'message' => 'Phone required'], 400);
        }
        return $this->getGuestByPhone($phone);
    }
}