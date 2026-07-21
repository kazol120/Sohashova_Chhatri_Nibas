<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectRegistration;



class ProjectRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        
        

        $createproject=ProjectRegistration::all();
         return response($createproject);

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
        ProjectRegistration::create($createdata);

      return response()->json([
            'message' => 'Project registration create successfully',
               'data'=> $createdata,
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
    public function update(Request $request,$id)
    {

        
        $updatedata = ProjectRegistration::findOrFail($id);
        $input = $request->except('_token');
        $updatedata->update($input);

       return response()->json([
            'message' => 'project registration update successfully',
            'data'=> $updatedata,
        ], 201);








    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        
        $projcctregistrationdelete=ProjectRegistration::find($id);
        $projcctregistrationdelete->delete();

          return response()->json([
            'message' => 'project registration delete successfully',
            'data' => $projcctregistrationdelete,
        ], 201);

    }
}
