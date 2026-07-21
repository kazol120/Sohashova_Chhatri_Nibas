<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectInformation;

class ProjectInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $projects=ProjectInformation::all();
        return response()->json($projects);
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
         
        ProjectInformation::create($createdata);
    

      return response()->json([
            'message' => 'Project created successfully',
           
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
    public function update(Request $request ,$id)

    {   



        $updatedata = ProjectInformation::findOrFail($id);
        $input = $request->except('_token');
        $updatedata->update($input);

       return response()->json([
            'message' => 'project information update successfully',
            'data'=> $updatedata,
        ], 201);

    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        
        $deleteprojectinformation=ProjectInformation::find($id);
        $deleteprojectinformation->delete();
        return response()->json([
            'message' => 'Project  information delete successfully',
            'data' => $deleteprojectinformation,
        ], 201);

    }
}
