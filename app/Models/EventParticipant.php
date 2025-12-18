<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    protected $table = 'event_participants';

    protected $fillable = [
        'event_id',
        'user_id',
        'role',
        'status'
    ];

    // 🔹 Mối quan hệ đến sự kiện
    public function event()
    {
        return $this->belongsTo(ClubEvent::class, 'event_id');
    }

    // 🔹 Mối quan hệ đến người dùng
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
