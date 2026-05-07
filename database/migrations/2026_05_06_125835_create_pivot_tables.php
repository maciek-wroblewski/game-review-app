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
        Schema::create('game_developer', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('developer_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('game_publisher', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publisher_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('game_platform', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('game_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['playing', 'played', 'dropped', 'wishlisted'])->nullable();
            $table->integer('personal_rating')->nullable();
            $table->enum('recommendation_rating', ['positive', 'neutral', 'negative'])->nullable();
            $table->text('review_text')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_list_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('order_num')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_list_user', function (Blueprint $table) {
            $table->foreignId('custom_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['collaborator', 'viewer'])->default('collaborator');
        });

        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('followable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
        Schema::dropIfExists('custom_list_user');
        Schema::dropIfExists('custom_list_game');
        Schema::dropIfExists('game_user');
        Schema::dropIfExists('game_platform');
        Schema::dropIfExists('game_genre');
        Schema::dropIfExists('game_publisher');
        Schema::dropIfExists('game_developer');
    }
};
