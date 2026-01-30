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
        // tabel session game
        Schema::create('penalty_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('goals_scored')->default(0);
            $table->integer('total_shots')->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('accuracy_percentage')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('points_earned');
        });

        // tabel poin user
        Schema::create('penalty_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('total_goals')->default(0);
            $table->integer('total_shots')->default(0);
            $table->integer('games_played')->default(0);
            $table->integer('best_score')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('best_streak')->default(0);
            $table->timestamps();

            $table->index('total_points');
        });

        // nambah kolom penalty poin di tabel user
        if (!Schema::hasColumn('users', 'penalty_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('penalty_points')->default(0)->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_game_sessions');
        Schema::dropIfExists('penalty_game_stats');
        
        if (Schema::hasColumn('users', 'penalty_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('penalty_points');
            });
        }
    }
};