<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubInvite extends Model
{
    protected $fillable = ['club_id', 'inviter_id', 'invitee_id', 'status'];

   public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    // Quan hệ với người mời
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    // Quan hệ với người nhận lời mời
    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }
}