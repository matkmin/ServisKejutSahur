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
            'action' => 'required|in:call,whatsapp',
            'status' => 'required|string', // e.g., 'initiated', 'completed'
        ]);

        $log = \App\Models\ActionLog::create([
            'agent_id' => $request->user()->id,
            'member_id' => $validated['member_id'],
            'action_type' => $validated['action'],
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Action logged successfully', 'log' => $log]);
    }
}
