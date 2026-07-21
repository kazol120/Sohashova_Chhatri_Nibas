<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Electricity;

class ElectricityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $electicitydata=Electricity::all();

        return $electicitydata;
        return response()->json($electicitydata);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $createdata=$request->except('_token');

        Electricity::create($createdata);

        return response()->json([
            "message"=>"electricity create successfully",
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {


        $updatedata=Electricity::findOrFail($id);
        $input= $request->except('_token');
        $updatedata->update($input);
        return response()->json([
            "message" => 'electticity data update successfully',
            "data" => $updatedata,
        ]);
        

    

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $electricitydelete=Electricity::find($id);
        $electricitydelete->delete();

          return response()->json([
            "message" => 'electticity data delete successfully',
           
        ]);
    }
}
