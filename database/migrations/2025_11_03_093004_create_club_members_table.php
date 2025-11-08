<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_members', function (Blueprint $table) {
            $table->id();

            // Liên kết đến bảng clubs
            $table->unsignedBigInteger('club_id');

            // Liên kết đến bảng users
            $table->unsignedBigInteger('user_id');

            // Vai trò trong CLB (ví dụ: owner, member, admin, v.v.)
            $table->string('role')->default('member');

            // Ngày tham gia
            $table->timestamp('joined_at')->useCurrent();

            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Không cho trùng 1 user 2 lần trong cùng CLB
            $table->unique(['club_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_members');
    }
};

