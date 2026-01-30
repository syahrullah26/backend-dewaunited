<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyGameSession extends Model
{ 

    protected $fillable = [
        'user_id',
        'goals_scored',
        'total_shots',
        'points_earned',
        'accuracy_percentage',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get user dari session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * kalkulasi persentase akurasi
     */
    public function calculateAccuracy(): int
    {
        if ($this->total_shots === 0) {
            return 0;
        }
        
        return (int) round(($this->goals_scored / $this->total_shots) * 100);
    }

    /**
     * kalkulasi poin dari gol dan akurasi
     */
    public function calculatePoints(): int
    {
        $basePoints = $this->goals_scored * 10; // 10 poin sekali gol
        
        // bonus dari akurasi
        $accuracy = $this->calculateAccuracy();
        if ($accuracy >= 80) {
            $bonusMultiplier = 1.5;
        } elseif ($accuracy >= 60) {
            $bonusMultiplier = 1.3;
        } elseif ($accuracy >= 40) {
            $bonusMultiplier = 1.1;
        } else {
            $bonusMultiplier = 1.0;
        }
        
        return (int) round($basePoints * $bonusMultiplier);
    }
}