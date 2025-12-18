<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubJoinRequest extends Model
{
    protected $fillable = ['club_id', 'user_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
