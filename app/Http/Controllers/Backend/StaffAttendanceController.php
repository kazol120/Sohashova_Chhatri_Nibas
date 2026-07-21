<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Staffs;
use App\Models\Backend\StaffAttendance;
use Illuminate\Support\Facades\Validator;

class StaffAttendanceController extends Controller
{
    public function index(){


        return view('backend.staffs.staffsattendance');
    }


   public function getstaffname()
    {
        $categories = Staffs::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }


public function search(Request $request)
{
    $query = $request->input('query');
    $staffs = Staffs::where('name', 'LIKE', "%{$query}%")->select('id', 'name')->limit(10)->get();
    return response()->json($staffs);
}



public function store(Request $request)

{
    $request->validate([
        'staff_id'       => 'required|exists:staffs,id',
        'start_datetime' => 'required|date',
        'end_datetime'   => 'required|date|after:start_datetime',
    ]);
    $attendance = StaffAttendance::create([
        'staff_id'       => $request->staff_id,
        'start_datetime' => $request->start_datetime,
        'end_datetime'   => $request->end_datetime,
    ]);
    return response()->json($attendance, 201);
}


public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => 'required|date',
            'end_datetime'   => 'required|date|after:start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $attendance = StaffAttendance::findOrFail($id);
        $attendance->start_datetime = $request->start_datetime;
        $attendance->end_datetime   = $request->end_datetime;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data'    => $attendance
        ]);
    }



public function GetAttendance(Request $request)
{
    $perPage   = (int) $request->get('per_page', 50);
    $search    = trim($request->get('search', ''));
    $startDate = $request->get('start_date');
    $endDate   = $request->get('end_date');
    $staffId   = $request->get('staff_id'); 
    $q = StaffAttendance::with('staff');
    if (!empty($staffId)) {
        $q->where('staff_id', $staffId);
    }
    if ($search !== '') {
        $q->whereHas('staff', function ($staffQuery) use ($search) {
            $staffQuery->where('employee_id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
    if (!empty($startDate) && !empty($endDate)) {
        $q->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);
    } elseif (!empty($startDate)) {
        $q->whereDate('created_at', '=', $startDate);
    } elseif (!empty($endDate)) {
        $q->whereDate('created_at', '=', $endDate);
    }
    $staffs = $q->orderBy('id', 'desc')->paginate($perPage);
    return response()->json($staffs);
}



}
