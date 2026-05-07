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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            
            // Review specific data
            $table->enum('type', ['recommendation', 'article', 'patch_note', 'announcement']);
            $table->integer('rating')->nullable(); // e.g., 1 for Good, 0 for Bad, or 1-10
            
            // You can add more review-specific fields here without bloating the 'posts' table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
