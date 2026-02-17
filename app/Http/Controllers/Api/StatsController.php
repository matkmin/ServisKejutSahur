<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActionLog;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'agent') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 1. Total Members
        $totalMembers = User::where('admin_id', $user->id)->count();

        // 2. Total Calls (Actions) - We count 'call' and 'whatsapp' actions
        $totalCalls = ActionLog::where('agent_id', $user->id)
            ->whereIn('action_type', ['call', 'whatsapp'])
            ->count();

        // 3. Recent Activity (Last 5 logs)
        $recentActivity = ActionLog::where('agent_id', $user->id)
            ->with('member:id,name,phone_number') // Eager load member details
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'member_name' => $log->member ? $log->member->name : 'Unknown',
                    'action_type' => $log->action_type,
                    'status' => $log->status,
                    'created_at' => $log->created_at->diffForHumans(), // Human readable time
                ];
            });

        // 4. Breakdown by Type (Call vs WhatsApp)
        $breakdown = ActionLog::where('agent_id', $user->id)
            ->select('action_type', DB::raw('count(*) as count'))
            ->groupBy('action_type')
            ->pluck('count', 'action_type');

        return response()->json([
            'total_members' => $totalMembers,
            'total_calls' => $totalCalls,
            'recent_activity' => $recentActivity,
            'breakdown' => $breakdown,
        ]);
    }
}
