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
            
            // Counter cache
            $table->integer('likes_count')->default(0); 
            
            $table->softDeletes();
            $table->timestamps();
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
