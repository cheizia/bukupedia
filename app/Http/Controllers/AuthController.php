<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|string|unique:users',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'user_id' => $validatedData['user_id'],
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => 'anggota', // Default anggota
        ]);

        return response()->json(['message' => 'Registrasi berhasil', 'user' => $user], 201);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $token = auth()->user()->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login berhasil', 'access_token' => $token, 'token_type' => 'Bearer']);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
