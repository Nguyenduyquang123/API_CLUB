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
        Schema::create('member_honors', function (Blueprint $table) {
           $table->increments('id');

            // club_id INT NOT NULL
            $table->integer('club_id');

            // user_id INT NOT NULL
            $table->integer('user_id');

            // achievement VARCHAR(255) NOT NULL
            $table->string('achievement');

            // description TEXT NULL
            $table->text('description')->nullable();

            // year INT NULL
            $table->integer('year')->nullable();

            // created_at & updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_honors');
    }
};
