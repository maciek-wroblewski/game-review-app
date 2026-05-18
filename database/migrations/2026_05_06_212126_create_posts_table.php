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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Creates hub_type and hub_id (games, user_profile, list)
            $table->nullableMorphs('hub');

            // For threaded replies
            $table->foreignId('parent_id')->nullable()->constrained('posts')->cascadeOnDelete();

            $table->text('body');
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_spoiler')->default(false);

            // Counter cache
            $table->integer('likes_count')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // --- ADD THESE PERFORMANCE INDEXES HERE ---
            $table->index(['parent_id', 'created_at']); // Optimizes global feeds & reply threads
            $table->index(['user_id', 'created_at']);   // Optimizes user profile recent posts
            $table->index(['hub_type', 'hub_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
