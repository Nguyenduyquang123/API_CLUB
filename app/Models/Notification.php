<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
 protected $fillable = [
        'user_id', 'from_user_id', 'type', 'club_id', 'title',
        'message', 'related_post_id', 'related_comment_id',
        'is_read'
    ];
    public function fromUser()     // người tạo thông báo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }
    


}
