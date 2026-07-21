<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherUtility;

class OtherUtilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datatable=OtherUtility::all();
        return response()->json($datatable);
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
     */ public function store(Request $request)
    {

        $createdata= $request->except('_token');
        OtherUtility::create($createdata);
        return response()->json([
            "message"=> "others utility create data successfully",
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
    public function update(Request $request ,$id)
    {
        $updatedata=OtherUtility::findOrFail($id);
        $input=$request->except('_token');
        $updatedata->update($input);
        return response()->json([
            'message'=> 'Others utility update data successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $deletedata=OtherUtility::find($id);
        $deletedata->delete();
        return response()->json([
            'message'=>'others utility delete successfully',
        ]);
    }
}
