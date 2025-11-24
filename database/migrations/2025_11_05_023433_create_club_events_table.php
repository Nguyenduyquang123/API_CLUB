<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('club_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('created_by');
            $table->string('title');
            $table->string('banner')->nullable();
            $table->text('description')->nullable();

            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location')->nullable();

           
            $table->integer('max_participants')->nullable();
         $table->boolean('notify')->default(false); 
            $table->boolean('require_registration')->default(false);
            $table->enum('status', ['upcoming', 'ongoing', 'ended', 'cancelled'])->default('upcoming');

            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('club_events');
    }
};
