<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Permission::all(), 'All permissions fetched');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions'
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'sanctum'
        ]);

        return $this->success($permission, 'Permission created successfully');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return $this->success([], 'Permission deleted successfully');
    }

    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($userId);
        $user->assignRole($request->role);

        return $this->success([], 'Role assigned to user');
    }

    public function assignPermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name'
        ]);

        $user = User::findOrFail($userId);
        $user->givePermissionTo($request->permission);

        return $this->success([], 'Permission assigned to user');
    }

    // public function assignPermissionToRole(Request $request)
    // {
    //     $request->validate([
    //         'role' => 'required|exists:roles,name',
    //         'permission' => 'required|exists:permissions,name'
    //     ]);

    //     $role = Role::where('name', $request->role)->first();
    //     $role->givePermissionTo($request->permission);

    //     return $this->success([], 'Permission assigned to role');
    // }


    public function assignMultiplePermissions(Request $request, $userId)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $user = User::findOrFail($userId);
        $user->givePermissionTo($request->permissions);

        return $this->success([], 'Multiple permissions assigned to user');
    }

    // public function assignMultiplePermissionsToRole(Request $request)
    // {
    //     $request->validate([
    //         'role' => 'required|exists:roles,name',
    //         'permissions' => 'required|array',
    //         'permissions.*' => 'exists:permissions,name'
    //     ]);

    //     $role = Role::where('name', $request->role)->first();
    //     $role->givePermissionTo($request->permissions);

    //     return $this->success([], 'Multiple permissions assigned to role');
    // }


    public function getUserRolesAndPermissions($id)
    {
        $user = User::findOrFail($id);

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return $this->success([
            'roles' => $roles,
            'permissions' => $permissions
        ], 'User roles and permissions fetched successfully');
    }



    public function getMyPermissionsAndRoles(Request $request)
    {
        $user = $request->user();

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return $this->success([
            'roles' => $roles,
            'permissions' => $permissions
        ], 'Roles and permissions fetched');
    }

}
