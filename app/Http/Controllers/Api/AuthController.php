<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau Password Salah'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'phone' => 'nullable|string|min:11|max:20',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);
        $path = null;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('uploads', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => $path,
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Berhasil Registrasi Akun',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'   => 'nullable|string|max:255',
            'phone'  => 'nullable|string|min:11|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('uploads', 'public');
            $user->avatar = $path;
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'message' => 'Berhasil Update Profile',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Berhasil Logout'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
