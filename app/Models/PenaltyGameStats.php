<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyGameStats extends Model
{

    protected $fillable = [
        'user_id',
        'total_points',
        'total_goals',
        'total_shots',
        'games_played',
        'best_score',
        'current_streak',
        'best_streak',
    ];

    /**
     * Get user stats ny
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * kalkulasi keseluruhan akurasi
     */
    public function getAccuracyAttribute(): int
    {
        if ($this->total_shots === 0) {
            return 0;
        }
        
        return (int) round(($this->total_goals / $this->total_shots) * 100);
    }

    /**
     * apdet stats sesudah game
     */
    public function updateAfterGame(PenaltyGameSession $session): void
    {
        $this->total_points += $session->points_earned;
        $this->total_goals += $session->goals_scored;
        $this->total_shots += $session->total_shots;
        $this->games_played += 1;

        // apdet best skor
        if ($session->points_earned > $this->best_score) {
            $this->best_score = $session->points_earned;
        }

        // apdet streak
        if ($session->goals_scored > 0) {
            $this->current_streak += 1;
            if ($this->current_streak > $this->best_streak) {
                $this->best_streak = $this->current_streak;
            }
        } else {
            $this->current_streak = 0;
        }

        $this->save();
    }
}