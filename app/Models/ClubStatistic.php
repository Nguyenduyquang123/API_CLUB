<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubStatistic extends Model
{
    protected $table = "club_statistics";

    protected $fillable = [
        'club_id', 'year', 'month',
        'new_members', 'total_posts',
        'total_comments', 'total_likes',
        'top_members', 'top_events'
    ];

    protected $casts = [
        'top_members' => 'array',
        'top_events' => 'array'
    ];
}
