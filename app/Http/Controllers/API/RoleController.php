<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class RoleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Role::all(), 'All roles fetched');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'sanctum'
        ]);

        return $this->success($role, 'Role created successfully');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return $this->success([], 'Role deleted successfully');
    }
    public function getUsersWithoutRoles()
    {
    $users = User::where('is_payment_done', 0) 
    ->whereNotIn('id', function ($query) {
        $query->select('model_id')
            ->from('model_has_roles')
            ->where('model_type', User::class);
    })
    ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Users without roles fetched successfully.',
            'data' => $users
        ]);
    }
}
