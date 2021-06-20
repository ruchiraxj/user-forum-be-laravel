<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use ErrorException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $action = 'user-register';

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        try {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'role_id' => 2
            ]);

            //$token = $user->createToken('myapptoken')->plainTextToken;

            $response = [
                'user' => $user,
                //'token' => $token
            ];

            Log::channel('access')->info($request->ip(), ['action' => $action, 'status' => 'success', 'data' => $fields]);

            return response($response, 201);
        } catch (\Throwable $th) {
            throw new ErrorException('System failed to create new record');
        }
    }


    public function login(Request $request)
    {
        //ID to identify the method
        $action = "user-login";

        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        try {
            
            $user = User::where('email', $fields['email'])->first();

            if (!$user || !Hash::check($fields['password'], $user->password)) {
                //log failed login
                Log::channel('access')->error($request->ip(), ['action' => $action, 'status' => 'fail', 'data' => ['email' => $fields['email']]]);

                return response([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;

            $response = [
                'user' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'token' => $token,
            ];

            Log::channel('access')->info($request->ip(), ['action' => $action, 'status' => 'success', 'data' => ['email' => $fields['email'], 'token' => $token]]);

            return response($response);

        } catch (\Throwable $th) {
            throw new ErrorException('System failed to create new record');
        }
    }

}
