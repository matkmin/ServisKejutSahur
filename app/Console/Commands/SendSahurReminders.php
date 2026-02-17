<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendSahurReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-sahur-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to agents for members with upcoming sahur time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current time (e.g., 04:30)
        // In simulation, we might check all active members or a specific time range
        // For MVP, let's find members whose sahur_time is within the next 30 minutes
        // Or simply matches current minute if running every minute.

        $now = now();
        $targetTime = $now->format('H:i');

        // Find members with this sahur time
        $members = \App\Models\User::where('role', 'user')
            ->where('sahur_time', $targetTime)
            ->where('status', 'active')
            ->get();

        if ($members->isEmpty()) {
            $this->info("No members found for sahur time: $targetTime");
            return;
        }

        // Group by Agent (admin_id)
        $grouped = $members->groupBy('admin_id');

        foreach ($grouped as $adminId => $groupMembers) {
            $agent = \App\Models\User::find($adminId);
            if ($agent) {
                // Prepare simpler data for notification
                $memberList = $groupMembers->map(function ($m) {
                    return [
                        'name' => $m->name,
                        'phone' => $m->phone_number
                    ];
                });

                $agent->notify(new \App\Notifications\SahurReminder($memberList, $targetTime));
                $this->info("Notified Agent {$agent->name} for " . $memberList->count() . " members.");
            }
        }
    }
}
