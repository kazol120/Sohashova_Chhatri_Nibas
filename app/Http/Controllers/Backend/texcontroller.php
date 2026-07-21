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





class RoomBookingHistoryController extends Controller
{




 public function getbookinghistory(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = trim($request->get('search', ''));

        $query = RoomBookingHistory::query()
            ->with([
                'floor:id,name',
                'room:id,room_no',
                'division:id,name',
                'district:id,name',
                'thana:id,name'
            ])
            ->where('status', 0);

        if ($search !== '') {
            $query->where(function ($qq) use ($search) {
                $qq->where('room_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nid', 'like', "%{$search}%")
                    ->orWhereHas('floor', function ($fq) use ($search) {
                        $fq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($rq) use ($search) {
                        $rq->where('room_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('division', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('district', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('thana', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rows = $query->orderByDesc('id')->get();

        $grouped = $rows->groupBy(function ($item) {
            return implode('|', [
                $item->phone ?? '',
                $item->check_in ?? '',
                $item->full_name ?? '',
                $item->email ?? '',
                $item->nid ?? '',
            ]);
        })->map(function ($items) {
            $first = $items->first();

            $roomNumbers = $items->map(function ($row) {
                return $row->room->room_no ?? $row->room_number ?? null;
            })
            ->filter()
            ->unique()
            ->sort(function ($a, $b) {
                return (int)$a <=> (int)$b;
            })
            ->values()
            ->implode(', ');

            $roomIds = $items->pluck('room_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'id' => $first->id,
                'group_key' => md5(
                    ($first->phone ?? '') .
                    ($first->check_in ?? '') .
                    ($first->full_name ?? '')
                ),
                'image' => $first->image,
                'full_name' => $first->full_name,
                'floor_name' => optional($first->floor)->name ?? '-',
                'room_numbers' => $roomNumbers,
                'room_ids' => $roomIds,
                'created_at' => $first->created_at ? $first->created_at->format('Y-m-d H:i:s') : null,
                'check_in' => $first->check_in,
                'check_out' => $first->check_out,
                'email' => $first->email,
                'nid' => $first->nid,
                'phone' => $first->phone,
                'pay_cash_in' => $first->pay_cash_in,
                'pay_online' => $first->pay_online,
                'payment_type' => $first->pay_cash_in ?: ($first->pay_online ?: '-'),
                'total_amount' => $items->sum(function ($row) {
                    return (float) ($row->payment_amount ?? 0);
                }),
                'division_name' => optional($first->division)->name ?? '-',
                'district_name' => optional($first->district)->name ?? '-',
                'thana_name' => optional($first->thana)->name ?? '-',
                'raw_rows_count' => $items->count(),
            ];
        })->values();

        $total = $grouped->count();
        $offset = ($page - 1) * $perPage;

        $paginatedItems = $grouped->slice($offset, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json([
            'current_page' => $paginator->currentPage(),
            'data' => $paginator->items(),
            'first_page_url' => $paginator->url(1),
            'from' => $total ? $offset + 1 : null,
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection(),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $request->url(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $offset + $paginatedItems->count(),
            'total' => $paginator->total(),
        ]);
    }







 public function getDivisions()
    {
        $divisions = Division::all();
        return response()->json($divisions);
    }

    // Get districts by division ID
    public function getDistrictsByDivision($divisionId)
    {
        $districts = District::where('division_id', $divisionId)->get();
        return response()->json($districts);
    }

    // Get thanas by district ID
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

        $query = RoomBookingHistory::query()
            ->with([
                'floor:id,name',
                'room:id,room_no',
                'division:id,name',
                'district:id,name',
                'thana:id,name'
            ])
            ->where('status', 0)->whereDate('created_at', today());;

        if ($search !== '') {
            $query->where(function ($qq) use ($search) {
                $qq->where('room_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nid', 'like', "%{$search}%")
                    ->orWhereHas('floor', function ($fq) use ($search) {
                        $fq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($rq) use ($search) {
                        $rq->where('room_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('division', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('district', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('thana', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rows = $query->orderByDesc('id')->get();

        $grouped = $rows->groupBy(function ($item) {
            return implode('|', [
                $item->phone ?? '',
                $item->check_in ?? '',
                $item->full_name ?? '',
                $item->email ?? '',
                $item->nid ?? '',
            ]);
        })->map(function ($items) {
            $first = $items->first();

            $roomNumbers = $items->map(function ($row) {
                return $row->room->room_no ?? $row->room_number ?? null;
            })
            ->filter()
            ->unique()
            ->sort(function ($a, $b) {
                return (int)$a <=> (int)$b;
            })
            ->values()
            ->implode(', ');

            $roomIds = $items->pluck('room_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'id' => $first->id,
                'group_key' => md5(
                    ($first->phone ?? '') .
                    ($first->check_in ?? '') .
                    ($first->full_name ?? '')
                ),
                'image' => $first->image,
                'full_name' => $first->full_name,
                'floor_name' => optional($first->floor)->name ?? '-',
                'room_numbers' => $roomNumbers,
                'room_ids' => $roomIds,
                'created_at' => $first->created_at ? $first->created_at->format('Y-m-d H:i:s') : null,
                'check_in' => $first->check_in,
                'check_out' => $first->check_out,
                'email' => $first->email,
                'nid' => $first->nid,
                'phone' => $first->phone,
                'pay_cash_in' => $first->pay_cash_in,
                'pay_online' => $first->pay_online,
                'payment_type' => $first->pay_cash_in ?: ($first->pay_online ?: '-'),
                'total_amount' => $items->sum(function ($row) {
                    return (float) ($row->payment_amount ?? 0);
                }),
                'division_name' => optional($first->division)->name ?? '-',
                'district_name' => optional($first->district)->name ?? '-',
                'thana_name' => optional($first->thana)->name ?? '-',
                'raw_rows_count' => $items->count(),
            ];
        })->values();

        $total = $grouped->count();
        $offset = ($page - 1) * $perPage;

        $paginatedItems = $grouped->slice($offset, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json([
            'current_page' => $paginator->currentPage(),
            'data' => $paginator->items(),
            'first_page_url' => $paginator->url(1),
            'from' => $total ? $offset + 1 : null,
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection(),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $request->url(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $offset + $paginatedItems->count(),
            'total' => $paginator->total(),
    ]);
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
        'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'check_in'         => 'required|date_format:Y-m-d',
        'check_out'        => 'required|date_format:Y-m-d|after_or_equal:check_in',
        'full_name'        => 'required|string|max:150',
        'phone'            => 'required|string|max:30',
        'email'            => 'nullable|email|max:150',
        'nid'              => 'nullable|string|max:50',
        'password'         => 'nullable|string|max:50',
        'division_id'      => 'nullable|numeric',
        'district_id'      => 'nullable|numeric',
        'thana_id'         => 'nullable|numeric',
        'payment'          => 'required|in:online,cash',
        'pay_method'       => 'nullable|string|max:30',
        'trx'              => 'nullable|string|max:80',
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
    $tempPassword = null;
    $user = null;

    if ($existingUser && !$request->filled('password')) {
        if ($request->ajax()) {
            return response()->json([
                'status'               => false,
                'message'              => 'Account already exists. Please enter your password.',
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
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $fileName = $cleanPhone . '.' . $ext;
        $folder = public_path('bookingsimage');

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
        $selectedAcStatus = [];
        $roomJsonData = [];
        $totalAmount = 0;
        $floorNames = [];

        foreach ($request->rooms_list as $roomNumber) {
            $room = Room::with('floor')
                ->where('room_no', $roomNumber)
                ->lockForUpdate()
                ->first();

            if (!$room) {
                throw new \Exception("Room not found: {$roomNumber}");
            }

            if ((int) $room->status === 1) {
                throw new \Exception("Room already booked: {$roomNumber}");
            }

            $singleRoomPrice = (float) ($room->price ?? 0);
            $floorName = $room->floor->name ?? '';

            $room->update([
                'status' => 1
            ]);

            $selectedRoomNumbers[] = $room->room_no;
            $selectedAcStatus[] = $room->ac_status;
            $floorNames[] = $floorName;

            $roomJsonData[] = [
                'floornumber' => (string) $floorName,
                'roomnumber'  => (string) $room->room_no,
                'price'       => $singleRoomPrice,
            ];

            $totalAmount += $singleRoomPrice;
        }

        if ($existingUser) {
            $existingUser->update([
                'name'   => $request->full_name ?: $existingUser->name,
                'email'  => $request->email ?: $existingUser->email,
                'phone'  => $request->phone ?: $existingUser->phone,
                'status' => 1,
            ]);

            $user = $existingUser;
        } else {
            $user = User::create([
                'name'          => $request->full_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'password'      => bcrypt($tempPassword),
                'status'        => 1,
                'temp_password' => $tempPassword,
            ]);
        }

        RoomBookingHistory::create([
        'image'                              => $imagePath,
        'floor_number_room_number_roomprice' => $roomJsonData,
        'full_name'                          => $request->full_name,
        'email'                              => $request->email,
        'phone'                              => '+88' . ltrim($request->phone, '+88'),
        'nid'                                => $request->nid,
        'password'                           => $existingUser ? $request->password : $tempPassword,
        'division_id'                        => $request->division_id,
        'district_id'                        => $request->district_id,
        'thana_id'                           => $request->thana_id,
        'pay_cash_in'                        => $request->payment === 'cash' ? 'cash' : null,
        'pay_online'                         => $request->payment === 'online'
                                                ? (($request->pay_method ?? 'Online') . ' | TRX: ' . ($request->trx ?? ''))
                                                : null,
        'payment_amount_total'               => $totalAmount,
        'check_in'                           => $request->check_in,
        'check_out'                          => $request->check_out,
        'status'                             => 0,
    ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        DB::commit();

        $divisionName = optional(Division::find($request->division_id))->name ?? '-';
        $districtName = optional(District::find($request->district_id))->name ?? '-';
        $thanaName = optional(Thana::find($request->thana_id))->name ?? '-';
        $bookingDateTime = Carbon::now('Asia/Dhaka')->format('d/m/Y g:i A');

        $mailData = [
            'full_name'      => $request->full_name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'nid'            => $request->nid,
            'floor_name'     => implode(', ', array_unique(array_filter($floorNames))),
            'room_numbers'   => implode(', ', $selectedRoomNumbers),
            'room_acstatus'  => implode(', ', $selectedAcStatus),
            'check_in'       => $request->check_in,
            'check_out'      => $request->check_out,
            'total_amount'   => $totalAmount,
            'division_name'  => $divisionName,
            'district_name'  => $districtName,
            'thana_name'     => $thanaName,
            'payment_type'   => $request->payment,
            'pay_method'     => $request->pay_method,
            'trx'            => $request->trx,
            'image_file'     => $imagePath,
            'create_at'      => $bookingDateTime,
            'room_json'      => $roomJsonData,
        ];

        try {
            Mail::to($adminEmail)->send(new RoomBookingMail($mailData));
        } catch (\Throwable $mailError) {
            \Log::error('Room booking email failed: ' . $mailError->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'status'          => true,
                'message'         => 'Booking successfully!',
                'account_created' => $accountCreated,
                'temp_password'   => $accountCreated ? $tempPassword : null,
                'user_status'     => 1,
                'redirect_url'    => url('/backend/dashboard'),
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

        $guest = \App\Models\Backend\RoomBookingHistory::whereRaw(
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


}
