<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'club_id', 'user_id', 'title', 'content',
        'is_pinned', 'notify_members'
    ];

    // 🔹 Liên kết đến người tạo bài viết (User)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔹 Liên kết đến câu lạc bộ
    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    // 🔹 Danh sách bình luận
    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }

    // // 🔹 Danh sách lượt thích
    // public function likes()
    // {
    //     return $this->hasMany(Like::class, 'post_id');
    // }
}
