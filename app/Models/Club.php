<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $table = 'clubs';

    protected $fillable = [
        'name',
        'description',
        'avatar_url',
        'invite_code',
        'is_public',
        'owner_id',
    ];

    // Mối quan hệ: CLB thuộc về 1 người tạo
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    

    // Nhiều thể loại (quan hệ nhiều-nhiều)
    public function categories()
    {
        return $this->belongsToMany(ClubCategory::class, 'club_category_club', 'club_id', 'category_id');
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'club_members', 'club_id', 'user_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }
    

}
