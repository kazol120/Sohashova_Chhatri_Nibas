<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;
    protected $permissionService;
    public function __construct(RoleService $roleService, PermissionService $permissionService)
    {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
        $this->middleware('auth');
        $this->middleware(['permission:role-index|role-create|role-edit|role-delete']);
//        $this->middleware('permission:role-create@create');
    }
    public function index()
    {
        $data['page_title'] = 'Roles';
        $data['roles'] = $this->roleService->roles();
        return view('backend.roles.index', $data);
    }
    public function create()
    {
        $data['page_title'] = 'Make New Roles';
        $data['permissions'] = $this->permissionService->permission();
        return view('backend.roles.create', $data);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $this->roleService->roleCreate($request);
        return redirect()->back()->with('success', 'Role created successfully');
    }
    public function show($id)
    {

    }
    public function edit($id)
    {
        $data['page_title'] = 'Edit Roles';
        $data['role'] = $this->roleService->roleById($id);
        $data['permissions'] = $this->permissionService->permission();
        return view('backend.roles.edit', $data);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$id,
        ]);
        $this->roleService->roleUpdate($request, $id);
        return redirect()->back()->with('success', 'Role update successfully');
    }
    public function destroy($id)
    {
        $this->roleService->roleDelete($id);
        return redirect()->back()->with('success', 'Role delete successfully');
    }
}
