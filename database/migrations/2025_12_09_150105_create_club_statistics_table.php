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
        Schema::create('club_statistics', function (Blueprint $table) {
           $table->id();
            $table->unsignedBigInteger('club_id');

            $table->integer('year');
            $table->integer('month');

            $table->integer('new_members')->default(0);
            $table->integer('total_posts')->default(0);
            $table->integer('total_comments')->default(0);
            $table->integer('total_likes')->default(0);

            $table->json('top_members')->nullable();
            $table->json('top_events')->nullable();

            $table->timestamps();
            $table->unique(['club_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_statistics');
    }
};
