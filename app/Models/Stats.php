<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{

    protected $fillable = [
        'match_id',
        'home_possession',
        'away_possession',
        'home_shots',
        'away_shots',
        'home_fouls',
        'away_fouls',
        'home_corner',
        'away_corner',
    ];

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }
}