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

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.',
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
            'email' => 'required|email|unique:users,email',
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
            'email' => $validated['email'],
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Agent registered successfully. Please check your email for verification.',
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
            'email' => 'nullable|email',
            'package' => 'nullable', // Expected to be JSON or array
        ]);

        $agent = User::where('referral_code', $validated['referral_code'])->first();

        // Parse package data if it comes as string (from JSON.stringify in frontend)
        $packageData = is_string($request->package) ? json_decode($request->package, true) : $request->package;

        $user = User::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make(Str::random(10)), // random password for members
            'role' => 'user',
            'admin_id' => $agent->id,
            'sahur_time' => $validated['sahur_time'],
            'status' => 'active',
            'email' => $validated['email'] ?? ($validated['phone_number'] . '@member.kejut'), // Use provided email or fallback
            'package' => $packageData['base'] ?? 'asas',
            'add_on' => $packageData['addons'] ?? [],
        ]);

        // Notify Agent via Email
        if ($agent->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($agent->email)->send(new \App\Mail\NewMemberNotification($user, $agent));
            } catch (\Exception $e) {
                // Log error but don't fail registration
                \Illuminate\Support\Facades\Log::error('Failed to send new member email to agent: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Member registered successfully',
            'user' => $user,
            'agent' => $agent->name,
        ]);
    }

    public function lookupAgent(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string'
        ]);

        $agent = User::where('referral_code', $request->referral_code)
            ->where('role', 'agent')
            ->first();

        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        return response()->json([
            'name' => $agent->name,
            'payment_qr' => $agent->payment_qr,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('admin'));
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

    public function uploadQr(Request $request)
    {
        $request->validate([
            'payment_qr' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('payment_qr')) {
            // Delete old QR if exists
            if ($user->payment_qr && \Illuminate\Support\Facades\Storage::exists('public/' . $user->payment_qr)) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $user->payment_qr);
            }

            $path = $request->file('payment_qr')->store('qr-codes', 'public');
            $user->payment_qr = $path;
            $user->save();

            return response()->json([
                'message' => 'QR Code uploaded successfully',
                'payment_qr' => $path,
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function deleteQr(Request $request)
    {
        $user = $request->user();

        if ($user->payment_qr) {
            if (\Illuminate\Support\Facades\Storage::exists('public/' . $user->payment_qr)) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $user->payment_qr);
            }

            $user->payment_qr = null;
            $user->save();

            return response()->json([
                'message' => 'QR Code deleted successfully',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'No QR Code to delete'], 400);
    }

    public function togglePaymentStatus(Request $request, $id)
    {
        $agent = $request->user();
        $member = User::where('id', $id)->where('admin_id', $agent->id)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $member->payment_status = $member->payment_status === 'paid' ? 'pending' : 'paid';
        $member->save();

        return response()->json([
            'message' => 'Payment status updated',
            'member' => $member
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3001') . '/dashboard/agent?verified=1');
        }

        $user->markEmailAsVerified();

        return redirect(env('FRONTEND_URL', 'http://localhost:3001') . '/dashboard/agent?verified=1');
    }
}
