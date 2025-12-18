<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    protected $table = 'comment_likes';
    protected $fillable = ['comment_id', 'user_id'];
      public $timestamps = false;
        // Quan hệ tới comment (nếu bạn muốn)

    // Quan hệ tới comment
    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    /**
     * Quan hệ tiện lợi tới post qua comment
     * Sử dụng hasOneThrough không được trong trường hợp này vì comment_id là ngoại khóa.
     * Cách chuẩn: dùng truy vấn quan hệ qua comment
     */
    public function post()
    {
        // Trả về quan hệ qua comment
        return $this->hasOneThrough(
            Post::class,       // Model đích
            PostComment::class, // Model trung gian
            'id',              // Khóa của PostComment (trung gian)
            'id',              // Khóa của Post (đích)
            'comment_id',      // Khóa ngoại của CommentLike (trỏ tới comment)
            'post_id'          // Khóa ngoại của PostComment (trỏ tới post)
        );
    }
}

