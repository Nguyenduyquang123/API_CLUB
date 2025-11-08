<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('club_event_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['participant', 'organizer'])->default('participant');
            $table->enum('status', ['invited', 'joined', 'declined'])->default('joined');
            $table->timestamp('joined_at')->useCurrent();

            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('club_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('club_event_members');
    }
};
