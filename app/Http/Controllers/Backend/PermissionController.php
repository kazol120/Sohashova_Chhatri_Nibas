<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    protected $permissionService;
    protected $roleService;
    public function __construct(PermissionService $permissionService, RoleService $roleService)
    {
        $this->permissionService = $permissionService;
        $this->roleService = $roleService;
        $this->middleware('auth');

    }
    public function index()
    {
        $data['page_title'] = 'Permissions';
        $data['permissions'] = $this->permissionService->permission();
        return view('backend.permissions.index', $data);
    }
    public function create()
    {
        $data['page_title'] = 'Create Permission';
        $data['roles'] = $this->roleService->roles();
        return view('backend.permissions.create', $data);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id', // Ensure each role ID exists in the roles table
        ]);
        $this->permissionService->permissionCreate($request);
        return redirect()->back()->with('success', 'Permission created successfully');

    }
    public function show($id)
    {

    }
    public function edit($id)
    {
        $data['page_title'] = 'Edit Permission';
        $data['roles'] = $this->roleService->roles();
        $data['permission'] = $this->permissionService->permissionById($id);
        $data['rolePermissions'] = DB::table('role_has_permissions')
            ->where('permission_id', $id) // Assuming $id is the permission ID being edited
            ->pluck('role_id') // Get the list of role IDs that have this permission
            ->toArray();

        return view('backend.permissions.edit', $data);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$id,
            'roles' => 'required|array',
            'roles.*' => 'required|exists:roles,id'
        ]);
        $this->permissionService->permissionUpdate($request, $id);
        return redirect()->back()->with('success', 'Permission updated successfully');
    }
    public function destroy($id)
    {
        $this->permissionService->permissionDelete($id);
        return redirect()->back()->with('success', 'Permission deleted successfully');
    }

}


