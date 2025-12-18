<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MemberHonor extends Model {
protected $table = 'member_honors';
protected $fillable = ['club_id','user_id','achievement','description','year'];
}