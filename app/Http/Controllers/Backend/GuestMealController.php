<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Deposit;
use App\Models\Backend\GuestMeal;
use App\Models\Backend\Meal;
use App\Models\Backend\Room;
use App\Models\Backend\RoomBookingHistory;
use App\Models\Backend\RoomSeat;
use App\Models\Backend\SpecialMeal;
use App\Models\User;
use App\Services\MealService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestMealController extends Controller
{
    protected $mealService;

    public function __construct(MealService $mealService)
    {
        $this->mealService = $mealService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of guest meals.
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $search = trim($request->input('search', ''));

        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        $query = GuestMeal::with(['user', 'booking', 'creator'])
            ->whereYear('date', Carbon::parse($selectedMonth)->year)
            ->whereMonth('date', Carbon::parse($selectedMonth)->month)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (!$isAdmin) {
            $query->where('user_id', $authUser->id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $allMonthlyGuestMeals = (clone $query)->get();
        $totalGuestMealsCount = $allMonthlyGuestMeals->sum('meal_count');
        $totalGuestMealsAmount = $allMonthlyGuestMeals->sum('amount');
        $totalResidentsInvolved = $allMonthlyGuestMeals->pluck('user_id')->unique()->count();

        $guestMeals = $query->paginate(20)->withQueryString();

        return view('backend.guest_meal.index', compact(
            'guestMeals',
            'selectedMonth',
            'search',
            'totalGuestMealsCount',
            'totalGuestMealsAmount',
            'totalResidentsInvolved',
            'isAdmin',
            'authUser'
        ));
    }

    /**
     * Show the form for creating a new guest meal.
     */
    public function create()
    {
        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        $activeResidents = $isAdmin ? $this->getActiveResidentsWithDetails() : [];
        $residentDetail = !$isAdmin ? $this->getSingleResidentDetail($authUser) : null;
        $defaultRate = $this->getCurrentMealRate(now()->format('Y-m-d'));

        return view('backend.guest_meal.create', compact('activeResidents', 'residentDetail', 'defaultRate', 'isAdmin', 'authUser'));
    }

    /**
     * Store a newly created guest meal in storage.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        $targetUserId = $isAdmin ? $request->input('user_id') : $authUser->id;

        $validated = $request->validate([
            'user_id'    => $isAdmin ? 'required|exists:users,id' : 'nullable',
            'date'       => 'required|date',
            'guest_name' => 'required|string|max:255',
            'meal_count' => 'required|numeric|min:0.5',
            'amount'     => 'required|numeric|min:0',
            'note'       => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($targetUserId);
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $booking = RoomBookingHistory::whereNull('today_check_out')
            ->when(!empty($cleanPhone10), function ($q) use ($cleanPhone10) {
                $q->where('phone', 'like', "%{$cleanPhone10}%");
            })
            ->latest('id')
            ->first();

        $guestMeal = GuestMeal::create([
            'user_id'    => $targetUserId,
            'booking_id' => $booking?->id,
            'date'       => $validated['date'],
            'guest_name' => $validated['guest_name'],
            'meal_count' => $validated['meal_count'],
            'amount'     => $validated['amount'],
            'note'       => $validated['note'] ?? null,
            'status'     => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('guest-meals.index')
            ->with('success', "Guest meal recorded successfully and ৳{$validated['amount']} adjusted against {$user->name}'s deposit.");
    }

    /**
     * Show the form for editing the specified guest meal.
     */
    public function edit($id)
    {
        $guestMeal = GuestMeal::findOrFail($id);
        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        if (!$isAdmin && (int)$guestMeal->user_id !== (int)$authUser->id) {
            abort(403, 'Unauthorized access to this guest meal record.');
        }

        $activeResidents = $isAdmin ? $this->getActiveResidentsWithDetails() : [];
        $residentDetail = !$isAdmin ? $this->getSingleResidentDetail($authUser) : null;
        $defaultRate = $this->getCurrentMealRate($guestMeal->date ? Carbon::parse($guestMeal->date)->format('Y-m-d') : now()->format('Y-m-d'));

        return view('backend.guest_meal.edit', compact('guestMeal', 'activeResidents', 'residentDetail', 'defaultRate', 'isAdmin', 'authUser'));
    }

    /**
     * Update the specified guest meal in storage.
     */
    public function update(Request $request, $id)
    {
        $guestMeal = GuestMeal::findOrFail($id);
        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        if (!$isAdmin && (int)$guestMeal->user_id !== (int)$authUser->id) {
            abort(403, 'Unauthorized access to this guest meal record.');
        }

        $targetUserId = $isAdmin ? $request->input('user_id') : $authUser->id;

        $validated = $request->validate([
            'user_id'    => $isAdmin ? 'required|exists:users,id' : 'nullable',
            'date'       => 'required|date',
            'guest_name' => 'required|string|max:255',
            'meal_count' => 'required|numeric|min:0.5',
            'amount'     => 'required|numeric|min:0',
            'note'       => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($targetUserId);
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $booking = RoomBookingHistory::whereNull('today_check_out')
            ->where(function ($q) use ($user, $cleanPhone10) {
                if (!empty($cleanPhone10)) {
                    $q->where('phone', 'like', "%{$cleanPhone10}%");
                }
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
            })
            ->latest('id')
            ->first();

        $guestMeal->update([
            'user_id'    => $targetUserId,
            'booking_id' => $booking?->id ?? $guestMeal->booking_id,
            'date'       => $validated['date'],
            'guest_name' => $validated['guest_name'],
            'meal_count' => $validated['meal_count'],
            'amount'     => $validated['amount'],
            'note'       => $validated['note'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('guest-meals.index')
            ->with('success', 'Guest meal record updated successfully.');
    }

    /**
     * Remove the specified guest meal from storage.
     */
    public function destroy($id)
    {
        $guestMeal = GuestMeal::findOrFail($id);
        $authUser = auth()->user();
        $isAdmin = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->hasRole('staffs') || $authUser->hasRole('manager'));

        if (!$isAdmin && (int)$guestMeal->user_id !== (int)$authUser->id) {
            abort(403, 'Unauthorized access.');
        }

        $guestMeal->delete();

        return redirect()->route('guest-meals.index')
            ->with('success', 'Guest meal record deleted successfully.');
    }

    /**
     * Helper to get active residents list with room & deposit balance details
     */
    private function getActiveResidentsWithDetails()
    {
        $activeBookings = RoomBookingHistory::whereNull('today_check_out')->get();
        $allRooms = Room::pluck('room_no', 'id')->toArray();
        $allSeats = RoomSeat::pluck('seat_no', 'id')->toArray();
        $currentMonth = now()->format('Y-m');

        $users = app(\App\Services\UserService::class)->usersWithRole();
        $residentsList = [];

        foreach ($users as $u) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone ?? '');
            $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $booking = $u->active_booking ?? null;
            if (!$booking) {
                $booking = $activeBookings->first(function($b) use ($cleanPhone10) {
                    $bClean = preg_replace('/[^0-9]/', '', $b->phone ?? '');
                    $bClean10 = strlen($bClean) >= 10 ? substr($bClean, -10) : $bClean;
                    return !empty($cleanPhone10) && !empty($bClean10) && $cleanPhone10 === $bClean10;
                });
            }

            // If user has an active booking or is in users list
            $roomSeatInfo = 'N/A';
            if ($booking) {
                $roomsJson = $booking->floor_number_room_number_roomprice;
                $roomsArr = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
                $parts = [];
                if (is_array($roomsArr)) {
                    foreach ($roomsArr as $rItem) {
                        $rNo = '';
                        $sNo = '';
                        if (!empty($rItem['room_id']) && isset($allRooms[$rItem['room_id']])) {
                            $rNo = $allRooms[$rItem['room_id']];
                        }
                        if (!empty($rItem['seat_id']) && isset($allSeats[$rItem['seat_id']])) {
                            $sNo = $allSeats[$rItem['seat_id']];
                        }
                        if (!empty($rItem['roomnumber'])) {
                            $parts[] = $rItem['roomnumber'];
                        } elseif ($rNo || $sNo) {
                            $parts[] = trim($rNo . '-' . $sNo, '-');
                        }
                    }
                }
                if (!empty($parts)) {
                    $roomSeatInfo = implode(', ', array_unique($parts));
                }
            }

            // Current Month Deposit & Meal Balance
            $depositTotal = Deposit::where('user_id', $u->id)
                ->whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->sum('amount');

            $residentsList[] = [
                'id'            => $u->id,
                'name'          => $u->name,
                'phone'         => $u->phone,
                'room_seat'     => $roomSeatInfo,
                'deposit_total' => (float)$depositTotal,
                'booking_id'    => $booking?->id,
            ];
        }

        usort($residentsList, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $residentsList;
    }

    /**
     * Helper to get single resident detail (room, phone, deposit)
     */
    private function getSingleResidentDetail($user)
    {
        $allRooms = Room::pluck('room_no', 'id')->toArray();
        $allSeats = RoomSeat::pluck('seat_no', 'id')->toArray();

        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanPhone10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $booking = RoomBookingHistory::whereNull('today_check_out')
            ->where(function ($q) use ($user, $cleanPhone10) {
                if (!empty($cleanPhone10)) {
                    $q->where('phone', 'like', "%{$cleanPhone10}%");
                }
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
            })
            ->latest('id')
            ->first();

        $roomSeatInfo = 'N/A';
        if ($booking) {
            $roomsJson = $booking->floor_number_room_number_roomprice;
            $roomsArr = is_string($roomsJson) ? json_decode($roomsJson, true) : $roomsJson;
            $parts = [];
            if (is_array($roomsArr)) {
                foreach ($roomsArr as $rItem) {
                    $rNo = '';
                    $sNo = '';
                    if (!empty($rItem['room_id']) && isset($allRooms[$rItem['room_id']])) {
                        $rNo = $allRooms[$rItem['room_id']];
                    }
                    if (!empty($rItem['seat_id']) && isset($allSeats[$rItem['seat_id']])) {
                        $sNo = $allSeats[$rItem['seat_id']];
                    }
                    if (!empty($rItem['roomnumber'])) {
                        $parts[] = $rItem['roomnumber'];
                    } elseif ($rNo || $sNo) {
                        $parts[] = trim($rNo . '-' . $sNo, '-');
                    }
                }
            }
            if (!empty($parts)) {
                $roomSeatInfo = implode(', ', array_unique($parts));
            }
        }

        $depositTotal = Deposit::where('user_id', $user->id)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->sum('amount');

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'phone'         => $user->phone,
            'room_seat'     => $roomSeatInfo,
            'deposit_total' => (float)$depositTotal,
            'booking_id'    => $booking?->id,
        ];
    }

    /**
     * Helper to get meal rate for given date
     */
    private function getCurrentMealRate($date)
    {
        $rates = $this->mealService->getMealRatesForDate($date);
        return $rates['full'] ?? 30.00;
    }

    public function show($id)
    {
        return redirect()->route('guest-meals.index');
    }
}
