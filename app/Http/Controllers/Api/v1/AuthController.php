<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Cookie;

class AuthController extends Controller
{
    /**
     * Login to Api
     *
     * @param /ILluminate/Http/Request $request
     * @return /Illuminate/Http/Response 
     **/
    public function login(Request $request)
    {
    	$request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = request(['username', 'password']);
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token =  $user->createToken('Personal Access Token')->accessToken;
            return response()
                ->json([
                    'status' 			=> 'success',
                    'token' 			=> $token,
                ], 200);
        } else {
            return response()->json(
                ['error' => 'invalid-credentials']
            );
        }
    }

    /**
     * Logout from system api
     * 
     * @param /ILluminate/Http/Request $request
     * @return /Illuminate/Http/Response 
     **/
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $cookie = Cookie::forget('_token');
        return response()->json([
            'message' => 'successful-logout'
        ])->withCookie($cookie);
    }
}
