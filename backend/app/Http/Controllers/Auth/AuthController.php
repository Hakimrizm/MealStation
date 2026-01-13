<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,tenant'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'token'   => $token,
            'role'    => $user->role,
            'user'    => $user
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login'], 401);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'role' => $user->role,
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            // opsional kalau mau ganti password
            'password' => 'nullable|string|min:6|confirmed', 
            // artinya frontend boleh kirim: password + password_confirmation
        ]);

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile berhasil diupdate',
            'user'    => $user->fresh(),
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function uploadQris(Request $request) {
        $request->validate([
            'qris' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'qris_name' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        // hapus lama
        if ($user->qris_image) Storage::disk('public')->delete($user->qris_image);

        $path = $request->file('qris')->store('qris', 'public');

        $user->update([
            'qris_image' => $path,
            'qris_name' => $request->qris_name,
        ]);

        return response()->json([
            'message' => 'QRIS berhasil diupload',
            'qris_image' => $path,
            'qris_name' => $user->qris_name,
        ]);
    }

    public function removeQris(Request $request) {
        $user = $request->user();
        if ($user->qris_image) Storage::disk('public')->delete($user->qris_image);

        $user->update(['qris_image' => null, 'qris_name' => null]);

        return response()->json(['message' => 'QRIS dihapus']);
    }
}
