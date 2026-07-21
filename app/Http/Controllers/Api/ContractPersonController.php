<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContractPerson;

class ContractPersonController extends Controller
{
    public function index()
    {
        $contactpoersondata = ContractPerson::all();
        return response()->json($contactpoersondata);
    }

    public function store(Request $request)
    {
        $createdata = $request->except('_token');

        $data = ContractPerson::create($createdata);

        return response()->json([
            "message" => "client information create successfully",
            "data" => $data,
        ], 201);
    }

    public function show($id)
    {
        $data = ContractPerson::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $updatedata = ContractPerson::findOrFail($id);

        $input = $request->except('_token');

        $updatedata->update($input);

        return response()->json([
            "message" => "contact person data update successfully",
            "data" => $updatedata,
        ]);
    }

    public function destroy($id)
    {
        $contactpersondeletedata = ContractPerson::findOrFail($id);

        $contactpersondeletedata->delete();

        return response()->json([
            "message" => "contact person data delete successfully",
        ]);
    }
}