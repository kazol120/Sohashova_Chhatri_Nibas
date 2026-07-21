<?php
namespace App\Services;
use Spatie\Permission\Models\Role;

class RoleService{
    public function roles()
    {
        return Role::latest()->get();
    }
    public function roleById($id)
    {
        return Role::findOrFail($id);
    }
    public function roleCreate($request)
    {
        $in = $request->except('_token');
        $role = Role::create($in);
        return $role;
    }
    public function roleUpdate($request, $id)
    {
        $role = $this->roleById($id);
        $in = $request->except('_token');
        $role->update($in);
        return $role;
    }
    public function roleDelete($id)
    {
        $role = $this->roleById($id);
        $role->permissions()->detach();
        $role->delete();
        return $role;
    }
}
