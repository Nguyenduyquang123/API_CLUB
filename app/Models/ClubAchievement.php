<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClubAchievement extends Model {
protected $table = 'club_achievements';
protected $fillable = ['club_id','title','description','year'];
}