<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SahurReminder extends Notification
{
    use Queueable;

    public $members;
    public $sahurTime;

    /**
     * Create a new notification instance.
     */
    public function __construct($members, $sahurTime)
    {
        $this->members = $members;
        $this->sahurTime = $sahurTime;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Waktu Sahur Hampir Tiba! 🌙',
            'message' => 'Members yang perlu dikejutkan untuk sahur pukul ' . $this->sahurTime . ':',
            'members' => $this->members, // Array of member names/phones
        ];
    }
}
