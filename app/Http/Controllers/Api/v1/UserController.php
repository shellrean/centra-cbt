<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;

use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function userLists()
    {
        $user = User::orderBy('created_at')->get();
        return ['data' => $user];
    }
    /**
	 * Get all user list
	 *
	 * @return /Illuminate/Http/Response
	 **/
    public function getUserLogin()
    {
    	$user = request()->user('api'); 
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if (request()->user('api')->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }
        $user['permission'] = $permissions; 
        return response()->json(['status' => 'success', 'data' => $user]);
    }
}
