<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\backed\HotelManagem;


class HotelManagementController extends Controller

{

    public function index(){
        return view('backend.hotelmanagement.management');
    }


    public function store(Request $request)
    {
        $hotelmangementsave= new HotelManagem();
        $hotelmangementsave->name =$request->name;
        $hotelmangementsave->save();
        return response()->json($hotelmangementsave);
    }



    public function getmanagement(Request $request){
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');
        $data = HotelManagem::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($data);
    }

    public function update(Request $request ,$id){

        $updatedata= HotelManagem::find($id);
        $updatedata->name =$request->name;
        $updatedata->update();
        return response()->json($updatedata);
    }
    public function destroy($id){
        $deletedate = HotelManagem::find($id);
        $deletedate->delete();
        return response()->json($deletedate);
    }



}
