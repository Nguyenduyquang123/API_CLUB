<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCalendarEventsTable extends Migration
{
    public function up()
    {
        Schema::create('user_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('club_id');
            $table->string('title');
            $table->text('description')->nullable();

            $table->dateTime('start');
            $table->dateTime('end')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_calendar_events');
    }
}
