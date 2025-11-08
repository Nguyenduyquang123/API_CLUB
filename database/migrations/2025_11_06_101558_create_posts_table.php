<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');      // CLB nào
            $table->unsignedBigInteger('user_id');      // Người tạo bài viết

            $table->string('title');
            $table->text('content');

            $table->boolean('is_pinned')->default(false);     // ghim bài
            $table->boolean('notify_members')->default(false); // gửi thông báo

            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::dropIfExists('posts');
    }
};
