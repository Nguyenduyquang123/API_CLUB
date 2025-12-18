<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubEvent extends Model
{
    protected $fillable = [
        'club_id',
        'created_by',
        'title',
        'banner',
        'description',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'notify',
        'require_registration',
        'status',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'club_event_members', 'event_id', 'user_id')
                    ->withPivot('role', 'status', 'joined_at')
                    ->withTimestamps();
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants', 'event_id', 'user_id')
                    ->withPivot(['status', 'role'])
                    ->withTimestamps();
    }

    public function joinedEvents()
    {
        return $this->belongsToMany(ClubEvent::class, 'event_participants')
                    ->withPivot('role')
                    ->withTimestamps();
    }

}
