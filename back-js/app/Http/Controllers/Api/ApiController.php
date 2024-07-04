<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    //register
    public function register(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => "1"
        ]);

        return response()->json([
            "status" => true,
            "message" => "User berhasil ditambah"
        ]);

    }

    //login api
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $request->email)->first();

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json(['token' => $token]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    //profile api
    public function profile(){
        $userData = auth()->user();
        return response()->json([
            "status" => true,
            "message" =>"Profile Data",
            "user" => $userData
        ]);
    }

    //refresh token
    public function refreshtoken(){
        $newToken = auth()->refresh();
        return response()->json([
            "status" => true,
            "message" =>"Token berhasil diubah",
            "token" => $newToken
        ]
        );

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
