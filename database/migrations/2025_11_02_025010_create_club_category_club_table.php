<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_category_club', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category');
            $table->unsignedBigInteger('club');
            $table->timestamps();
            $table->foreign('category')->references('id')->on('club_categories')->onDelete('cascade');
            $table->foreign('club')->references('id')->on('clubs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_category_club');
    }
};
