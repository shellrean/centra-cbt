<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;
use App\User;

class RolePermissionController extends Controller
{
	/**
	 * Get all roles
	 *
	 * @return /Illuminate/Http/Response
	 **/
    public function getAllRole()
    {
    	$roles = Role::all();
    	return response()->json(['status' => 'success', 'data' => $roles]);
    }

    /**
	 * Get all permissions
	 *
	 * @return /Illuminate/Http/Response
	 **/
    public function getallPermission()
    {
    	$permission = Permission::all();
    	return response()->json(['status' => 'success' , 'data' => $permission]);
    }

    /**
	 * Get all permission of role
	 *
	 * @param /Illuminate/Http/Request
	 * @return /Illuminate/Http/Response
	 **/
    public function getRolePermission(Request $request)
    {
    	$hasPermission = DB::table('role_has_permissions')
            ->select('permissions.name')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_id', $request->role_id)->get();
        return response()->json(['status' => 'success', 'data' => $hasPermission]);
    }

    /**
	 * Set permission for role
	 *
	 * @param /Illuminate/Http/Request
	 * @return /Illuminate/Http/Response
	 **/
    public function setRolePermission(Request $request)
    {
    	$this->validate($request, [
    		'role_id'	=> 'required|exists:roles,id'
    	]);

    	$role = Role::find($request->role_id);
    	$role->syncPermissions($request->permissions);

    	return response()->json(['status' => 'success']);
    } 

    /**
	 * Set role for user
	 *
	 * @param /Illuminate/Http/Request
	 * @return /Illuminate/Http/Response
	 **/
    public function setRoleUser(Request $request)
    {
    	$this->validate($request, [
    		'user_id'		=> 'required|exists:users,id',
    		'role'			=> 'required'
    	]);

    	$user = User::find($request->user_id);
    	$user->syncRoles([$request->role]);

    	return response()->json(['status' => 'success']);
    }
}
