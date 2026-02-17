<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->role !== 'agent') {
            return response()->json([
                'message' => 'Only agents can login here.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function registerAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|string|min:6',
        ]);

        // Generate unique referral code
        $base = substr(preg_replace('/[^A-Za-z]/', '', strtoupper($validated['name'])), 0, 3);
        if (strlen($base) < 3)
            $base = str_pad($base, 3, 'X');

        $referralCode = $base . rand(100, 999);
        while (User::where('referral_code', $referralCode)->exists()) {
            $referralCode = $base . rand(100, 999);
        }

        $user = User::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'role' => 'agent',
            'referral_code' => $referralCode,
            'email' => $validated['phone_number'] . '@kejut.sahur',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Agent registered successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function registerMember(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'referral_code' => 'required|string|exists:users,referral_code',
            'sahur_time' => 'required|date_format:H:i',
        ]);

        $agent = User::where('referral_code', $validated['referral_code'])->first();

        $user = User::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make(Str::random(10)), // random password for members
            'role' => 'user',
            'admin_id' => $agent->id,
            'sahur_time' => $validated['sahur_time'],
            'status' => 'active',
            'email' => $validated['phone_number'] . '@member.kejut',
        ]);

        return response()->json([
            'message' => 'Member registered successfully',
            'user' => $user,
            'agent' => $agent->name,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
    public function getMembers(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'agent') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $members = User::where('admin_id', $user->id)->get();

        return response()->json($members);
    }

    public function updateSahurTime(Request $request)
    {
        $validated = $request->validate([
            'sahur_time' => 'required|date_format:H:i',
        ]);

        $user = $request->user();
        $user->sahur_time = $validated['sahur_time'];
        $user->save();

        return response()->json(['message' => 'Sahur time updated successfully', 'user' => $user]);
    }

    public function getNotifications(Request $request)
    {
        // Get unread notifications
        return response()->json($request->user()->unreadNotifications);
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Notifications marked as read']);
    }
}
