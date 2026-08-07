<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|string|unique:users',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $user = \App\Models\User::create([
            'user_id' => $validatedData['user_id'],
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role']
        ]);

        return response()->json(['message' => 'Registrasi berhasil', 'user' => $user], 201);
    }
}
