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
        Schema::create('club_achievements', function (Blueprint $table) {
            $table->increments('id');

            // club_id INT NOT NULL
            $table->integer('club_id');

            // title VARCHAR(255) NOT NULL
            $table->string('title');

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
        Schema::dropIfExists('club_achievements');
    }
};
