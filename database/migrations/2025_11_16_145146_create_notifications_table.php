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
       Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
             $table->unsignedBigInteger('from_user_id')->nullable()->after('user_id');
            $table->string('type'); // comment, like, announcement, event
            $table->unsignedBigInteger('club_id')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('related_post_id')->nullable();
            $table->unsignedBigInteger('related_comment_id')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();

            $table->foreign('from_user_id')
          ->references('id')->on('users')
          ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
