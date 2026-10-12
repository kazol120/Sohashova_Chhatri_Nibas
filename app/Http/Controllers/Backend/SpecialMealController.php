<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\SpecialMeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SpecialMealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $specialMeals = SpecialMeal::orderBy('date', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data'   => $specialMeals,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'date'      => 'required|date',
            'full_rate' => 'required|numeric|min:0',
            'half_rate' => 'nullable|numeric|min:0',
            'title'     => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $formattedDate = Carbon::parse($request->date)->format('Y-m-d');

        $specialMeal = SpecialMeal::updateOrCreate(
            ['date' => $formattedDate],
            [
                'title'     => $request->title,
                'full_rate' => $request->full_rate,
                'half_rate' => $request->filled('half_rate') ? $request->half_rate : (round($request->full_rate / 2, 2)),
                'status'    => $request->has('status') ? (bool)$request->status : true,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Special Meal saved successfully!',
            'data'    => $specialMeal,
        ]);
    }

    public function destroy($id)
    {
        $specialMeal = SpecialMeal::findOrFail($id);
        $specialMeal->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Special Meal deleted successfully!',
        ]);
    }

    public function toggleStatus($id)
    {
        $specialMeal = SpecialMeal::findOrFail($id);
        $specialMeal->status = !$specialMeal->status;
        $specialMeal->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Status updated successfully!',
            'data'    => $specialMeal,
        ]);
    }
}
