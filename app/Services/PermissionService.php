<?php
namespace App\Services;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionService{

    public function permission()
    {
        return Permission::latest()->get();
    }
    //for user create hide some permission that are confidential
    public function userPermission()
    {
        return Permission::whereNotIn('name', [
            'index', 'create','store','edit','update', 'destroy',
            'role-index', 'role-create', 'role-edit', 'role-delete',
            'permission-index', 'permission-create', 'permission-edit', 'permission-delete',
            
            ])->get();
    }
    public function permissionById($id)
    {
        return Permission::findOrFail($id);
    }
    public function permissionCreate(Request $request)
    {
        $in = $request->except('_token');
        $permission = Permission::create($in);//save the permission
        $roleIds = $request->get('roles');
        if (count($roleIds) > 0)
        {
            $role = Role::find($roleIds); //find role
            $permission->assignRole($role); // role attach the permission
        }
        return $permission;
    }
    public function permissionUpdate(Request $request, $id)
    {
        $permission = $this->permissionById($id);
        $in = $request->except('_token');
        $permission->update($in);
        $roleIds = $request->get('roles');
        if (count($roleIds) > 0)
        {
            $role = Role::find($roleIds); //find role
            $permission->roles()->sync($role); // Sync ensures roles are updated (add/remove)
        }
        return $permission;
    }
    public function permissionDelete($id)
    {
        $permission = $this->permissionById($id);

        $permission->roles()->detach();
        $permission->delete();
        return $permission;
    }
}
