<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCalendarEvent extends Model
{
    protected $table = 'user_calendar_events';

    protected $fillable = [
        'user_id',
         'event_id', 
         'club_id',
        'title',
        'description',
        'start',
        'end',
    ];
}
