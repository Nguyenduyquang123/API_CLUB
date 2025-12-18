<?php
namespace App\Models;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Model;

class GalleryAlbum extends Model
{
    protected $fillable = [
        'club_id',
        'title',
        'description',
        'cover_image'
    ];

    public function images()
    {
        return $this->hasMany(GalleryImage::class, 'album_id');
    }   
}