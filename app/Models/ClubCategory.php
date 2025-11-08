<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubCategory extends Model
{
    protected $table = 'club_categories';

    protected $fillable = ['name'];

    public $timestamps = false;

    // Một thể loại có thể thuộc nhiều CLB
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_category_club', 'category_id', 'club_id');
    }
}
