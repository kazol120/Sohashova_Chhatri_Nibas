<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClientInformation;


class ClientInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientservice=ClientInformation::all();
        return response()->json($clientservice);
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
        ClientInformation::create($createdata);
        return response()->json([
            "message" => 'client information create successfully',
            "data" => $createdata,
        ], 201);

        
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
    public function update(Request $request, $id)
    {

       
        $updatedata = ClientInformation::findOrFail($id);
        $input = $request->except('_token');
        $updatedata->update($input);

       return response()->json([
            'message' => 'client information update successfully',
            'data'=> $updatedata,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $clientinformationdelete=ClientInformation::find($id);
        $clientinformationdelete->delete();
        return response()->json([
            'message' => 'client information delete successfully',
            'data' => $clientinformationdelete,
        ], 201);

    }
}
