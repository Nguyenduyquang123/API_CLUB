<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GalleryImage extends Model
{
    protected $fillable = [
        'album_id',
        'image_url',
        'caption'
    ];

    public function album()
    {
        return $this->belongsTo(GalleryAlbum::class, 'album_id');
    }
}
