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
use App\Models\Absen;

class ApiController extends Controller
{
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


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $request->email)->first();


            try {
                $absen = new Absen();
                $absen->email = $user->email;
                $absen->jam_masuk = now()->format('H:i:s');
                $absen->tanggal = now()->toDateString();
                $absen->save();
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'token' => $token,
                    'message' => 'Login successful',
                    'absen' => $absen
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save attendance',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function profile(){
        $userData = auth()->user();
        return response()->json([
            "status" => true,
            "message" =>"Profile Data",
            "user" => $userData
        ]);
    }

    public function absen(){
        $absen = Absen::get();

        return response()->json([
            "status" => true,
            "kehadiran" => $absen
        ]);
    }

public function storeAbsen(Request $request)
{
    try {
        $user = Auth::user();
        $absen = new Absen();
        $absen->email = $user->email;
        $absen->jam_masuk = now()->format('H:i:s');
        $absen->tanggal = now()->toDateString();
        $absen->save();

        return response()->json([
            "status" => true,
            "message" => "Absensi berhasil disimpan",
            "absen" => $absen
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            "status" => false,
            "message" => "Gagal menyimpan absensi",
            "error" => $e->getMessage()
        ], 500);
    }
}

public function deleteAbsen($id)
{
    $absen = Absen::find($id);

    if ($absen) {
        $absen->delete();
        return response()->json([
            "status" => true,
            "message" => "Data berhasil dihapus"
        ]);
    } else {
        return response()->json([
            "status" => false,
            "message" => "Data tidak ditemukan"
        ], 404);
    }
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
