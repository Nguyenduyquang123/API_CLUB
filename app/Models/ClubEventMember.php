<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubEventMember extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'role',
        'status',
        'joined_at',
    ];

    public function event()
    {
        return $this->belongsTo(ClubEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
