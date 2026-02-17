<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActionLog;

class ActionController extends Controller
{
    public function logAction(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'action_type' => 'required|in:call,whatsapp',
            'status' => 'required|string', // e.g., 'initiated', 'completed'
        ]);

        $log = \App\Models\ActionLog::create([
            'agent_id' => $request->user()->id,
            'member_id' => $validated['member_id'],
            'action_type' => $validated['action_type'],
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Action logged successfully', 'log' => $log]);
    }

    public function toggleComplete(Request $request, $id)
    {
        $agent = $request->user();
        $member = \App\Models\User::where('id', $id)->where('admin_id', $agent->id)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        if ($member->payment_status !== 'paid') {
            return response()->json(['message' => 'Member has not paid yet.'], 403);
        }

        // Check if completed today using Carbon/PHP date logic on server side or just string comparison
        $isCompletedToday = $member->last_completed_at && $member->last_completed_at->isToday();

        if ($isCompletedToday) {
            $member->last_completed_at = null; // Untick
        } else {
            $member->last_completed_at = now(); // Tick
        }

        $member->save();

        return response()->json([
            'message' => 'Completion status updated',
            'member' => $member
        ]);
    }
}
