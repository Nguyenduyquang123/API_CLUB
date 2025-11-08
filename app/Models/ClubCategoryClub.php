<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubCategoryClub extends Model
{
    protected $table = 'club_category_club';

    protected $fillable = ['club_id', 'category_id'];

    public $timestamps = false;
}
