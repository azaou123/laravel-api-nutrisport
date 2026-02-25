<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER (Customer)
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => 'customer'
        ]);

        return response()->json([
            'message' => 'Customer registered successfully'
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN CUSTOMER (6h)
    |--------------------------------------------------------------------------
    */
    public function loginCustomer(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // Attempt login and get token
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = JWTAuth::user();

            if ($user->type !== 'customer') {
                return response()->json(['error' => 'Unauthorized role'], 403);
            }

            // Optionally, set a custom TTL for this token
            JWTAuth::factory()->setTTL(360);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return $this->respondWithToken($token);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN AGENT (8h)
    |--------------------------------------------------------------------------
    */
    public function loginAgent(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = JWTAuth::user();

            if ($user->type !== 'agent') {
                return response()->json(['error' => 'Unauthorized role'], 403);
            }

            JWTAuth::factory()->setTTL(480); // custom TTL in minutes

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return $this->respondWithToken($token);
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN RESPONSE
    |--------------------------------------------------------------------------
    */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REFRESH TOKEN
    |--------------------------------------------------------------------------
    */
    public function refresh()
    {
        try {
            return $this->respondWithToken(JWTAuth::refresh());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        $user = JWTAuth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Wrong password'], 400);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */    
    public function updateProfile(Request $request){
        $user = JWTAuth::user();
        $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        ]);
        if($request->has('name')){
            $user->name = $request->name;
        }
        if($request->has('email')){
            $user->email = $request->email;
        }
        $user->save();
        return response()->json(['message' => 'Profile Updated Successfully', 'user'=>$user]);
    }
}
