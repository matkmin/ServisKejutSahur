<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'member_id',
        'action_type', // 'call' or 'whatsapp'
        'status',      // 'initiated'
    ];
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
