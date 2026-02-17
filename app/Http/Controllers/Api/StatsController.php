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

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Base query for logs
        $logsQuery = ActionLog::where('agent_id', $user->id);

        if ($startDate && $endDate) {
            $logsQuery->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }

        // 1. Total Members (Not affected by date filter usually, but kept as is)
        $totalMembers = User::where('admin_id', $user->id)->count();

        // 2. Total Calls (Actions) - Filtered
        $totalCalls = (clone $logsQuery) // Clone to not affect other queries
            ->whereIn('action_type', ['call', 'whatsapp'])
            ->count();

        // 3. Recent Activity (Last 5 logs) - Filtered
        $recentActivity = (clone $logsQuery)
            ->with('member:id,name,phone_number')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'member_name' => $log->member ? $log->member->name : 'Unknown',
                    'action_type' => $log->action_type,
                    'status' => $log->status,
                    'created_at' => $log->created_at->diffForHumans(),
                ];
            });

        // 4. Breakdown by Type - Filtered
        $breakdown = (clone $logsQuery)
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
