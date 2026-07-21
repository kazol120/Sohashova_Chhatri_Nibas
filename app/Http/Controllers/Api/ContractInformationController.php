<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContractInformation;

class ContractInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $contractinformationdata=ContractInformation::all();
        return response()->json($contractinformationdata);
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




         $createdata = $request->except('_token');
        ContractInformation::create($createdata);
    

      return response()->json([
            'message' => 'contract information created successfully',
           
        ], 201);




    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $udpatedata=ContractInformation::findOrFail($id);
        $inpute=$request->except('_token');
        $udpatedata->update($inpute);


        return response()->json([
            "message"=> 'Contractiformation data update successfully ',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $conractinformationdelete=ContractInformation::find($id);
        $conractinformationdelete->delete();

        return response()->json([
            "message"=> 'Contractiformation delete successfully',

        ]);
    }
}
