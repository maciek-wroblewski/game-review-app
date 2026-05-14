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
        Schema::table('user_settings', function (Blueprint $table) {

            $table->string('profile_visibility')
                ->default('public');

            $table->string('playlist_visibility')
                ->default('public');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {

            $table->dropColumn([
                'profile_visibility',
                'playlist_visibility',
            ]);

        });
    }
};