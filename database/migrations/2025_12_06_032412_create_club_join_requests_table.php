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
        Schema::create('club_join_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('club_id')->unsigned();
            $table->integer('user_id')->unsigned();
            
            // Định nghĩa cột ENUM cho status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            $table->timestamps();
            
             // Ràng buộc khóa ngoại
         //   $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
        //    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Tùy chọn: Thêm chỉ mục để tối ưu hiệu suất truy vấn
            $table->index(['club_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_join_requests');
    }
};
