<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MealService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MealController extends Controller
{
    protected $mealService;
    protected $userService;

    public function __construct(MealService $mealService, UserService $userService)
    {
        $this->mealService = $mealService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Meal Management';
        $data['users'] = $this->userService->usersWithRole();
        $data['selectedDate'] = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : now()->format('Y-m-d');

        $mealResult = $this->mealService->todayOrPreviousMealStatus($data['selectedDate']);

        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['summary'] = $this->mealService->mealStatusSummary($data['users'], $data['meals']);

        return view('backend.meal.index', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Today Meal Create';
        $currentUser = auth()->user();
        $data['users'] = $this->userService->usersWithRole();
        $mealResult = $this->mealService->todayOrPreviousMealStatus(now());
        $data['meals'] = $mealResult['meals'];
        $data['used_date'] = $mealResult['used_date'];
        $data['is_fallback'] = $mealResult['is_fallback'];
        $data['todaySummary'] = $this->mealService->todayMealSummary(now());
        $data['canSaveMeal'] = true;
        return view('backend.meal.create', $data);
    }

    public function mealHistory(Request $request)
    {
        $data['page_title'] = 'Meal History';
        $data['users'] = $this->userService->usersWithRole();

        $selectedMonth = $request->month
            ? Carbon::parse($request->month . '-01')->format('Y-m')
            : now()->format('Y-m');

        $mealHistory = $this->mealService->monthlyMealHistory($selectedMonth, $data['users']);

        $data['selectedMonth'] = $mealHistory['selectedMonth'];
        $data['half_rate'] = $mealHistory['half_rate'];
        $data['full_rate'] = $mealHistory['full_rate'];
        $data['mealSummaryByUser'] = $mealHistory['mealSummaryByUser'];
        $data['summary'] = $mealHistory['summary'];

        return view('backend.meal.meal-history', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'meal' => 'required|array',
            'meal.*.half_meal' => 'nullable|in:0,1',
            'meal.*.full_meal' => 'nullable|in:0,1',
        ]);
        
        $result = $this->mealService->mealStore($request);
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', 'Today meal saved successfully.');
    }
}
