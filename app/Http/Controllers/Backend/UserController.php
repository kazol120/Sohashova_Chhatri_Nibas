<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    protected $permissionService;
    protected $roleService;

    public function __construct(UserService $userService, PermissionService $permissionService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->permissionService = $permissionService;
        $this->roleService = $roleService;
        $this->middleware(['auth','permission:user-index|user-create|user-edit|user-delete']);
    }

    public function index()
    {
        $data['page_title'] = 'User List';
        $data['users'] = $this->userService->user();
        return view('backend.user.index', $data);
    }
    public function create()
    {
        $data['page_title'] = 'Create User';
        $data['permissions'] = $this->permissionService->userPermission();
        $data['roles'] = $this->roleService->roles();
        return view('backend.user.create', $data);

    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
        ]);
        if (!$validatedData)
        {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        $this->userService->userCreate($request);
        return redirect()->route('user.index')->with('success', 'User created successfully');
    }
    public function show($id)
    {
        return redirect()->route('user.index');
    }
    public function edit($id)
    {
        $data['page_title'] = 'Edit User';
        $data['permissions'] = $this->permissionService->userPermission();
        $data['roles'] = $this->roleService->roles();
        $data['user'] = $this->userService->userById($id);
        return view('backend.user.edit', $data);

    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$id,
        ]);
        if (!$validatedData)
        {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        $this->userService->userUpdate($request, $id);
        return redirect()->route('user.index')->with('success', 'User updated successfully');

    }
    public function destroy($id)
    {
        $this->userService->userDelete($id);
        return redirect()->route('user.index')->with('success', 'User deleted successfully');

    }
}
