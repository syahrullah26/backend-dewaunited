<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Stats;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Team;

class MatchGameController extends Controller
{
    //==================
    // GET /api/matches
    //==================
    public function index()
    {
        $matches = MatchGame::with([
            'homeTeam:id,name,logo',
            'awayTeam:id,name,logo',
        ])
            ->orderBy('match_date')
            ->paginate(6);

        $matches->getCollection()->transform(function ($match) {
            return [
                'id' => $match->id,
                'home_team' => [
                    'id' => $match->homeTeam->id,
                    'name' => $match->homeTeam->name,
                    'logo_url' => $match->homeTeam->logo
                        ? asset('storage/' . $match->homeTeam->logo)
                        : null,
                ],
                'away_team' => [
                    'id' => $match->awayTeam->id,
                    'name' => $match->awayTeam->name,
                    'logo_url' => $match->awayTeam->logo
                        ? asset('storage/' . $match->awayTeam->logo)
                        : null,
                ],
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
                'match_date' => $match->match_date,
                'stadium' => $match->stadium,
                'status' => $match->status,
            ];
        });

        return response()->json($matches);
    }

    //======================
    // GET /api/matches/{id}
    //======================
    public function getMatchByID($id)
    {
        $match = MatchGame::with([
            'homeTeam:id,name,logo',
            'awayTeam:id,name,logo',
        ])->find($id);

        if (!$match) {
            return response()->json([
                'message' => 'Match not found'
            ], 404);
        }

        return response()->json([
            'id' => $match->id,
            'home_team' => [
                'id' => $match->homeTeam->id,
                'name' => $match->homeTeam->name,
                'logo_url' => $match->homeTeam->logo
                    ? asset('storage/' . $match->homeTeam->logo)
                    : null,
            ],
            'away_team' => [
                'id' => $match->awayTeam->id,
                'name' => $match->awayTeam->name,
                'logo_url' => $match->awayTeam->logo
                    ? asset('storage/' . $match->awayTeam->logo)
                    : null,
            ],
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'match_date' => $match->match_date,
            'stadium' => $match->stadium,
            'status' => $match->status,
        ]);
    }

    //=========================
    // GET /api/matches/history
    //=========================
    public function matchHistory() 
    {
        $matches = MatchGame::with([
            'homeTeam:id,name,logo',
            'awayTeam:id,name,logo',
        ])
            ->where('status', 'history')
            ->orderBy('match_date', 'desc')
            ->get();

        $transformedMatches = $matches->map(function ($match) {
            return [
                'id' => $match->id,
                'home_team' => [
                    'id' => $match->homeTeam->id,
                    'name' => $match->homeTeam->name,
                    'logo_url' => $match->homeTeam->logo
                        ? asset('storage/' . $match->homeTeam->logo)
                        : null,
                ],
                'away_team' => [
                    'id' => $match->awayTeam->id,
                    'name' => $match->awayTeam->name,
                    'logo_url' => $match->awayTeam->logo
                        ? asset('storage/' . $match->awayTeam->logo)
                        : null,
                ],
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
                'match_date' => $match->match_date,
                'stadium' => $match->stadium,
                'status' => $match->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedMatches
        ]);
    }

    //============================
    // GET /api/matches/upcoming
    //============================
    public function nextUpcomingMatch()
    {
        $match = MatchGame::with(['homeTeam:id,name,logo', 'awayTeam:id,name,logo'])
            ->where('status', 'upcoming')
            ->whereDate('match_date', '>=', now()->toDateString())
            ->orderBy('match_date', 'asc')
            ->first();

        if (!$match) {
            return response()->json([
                'message' => 'Match not found'
            ], 404);
        }

        return response()->json([
            'id' => $match->id,
            'home_team' => [
                'id' => $match->homeTeam->id,
                'name' => $match->homeTeam->name,
                'logo_url' => $match->homeTeam->logo
                    ? asset('storage/' . $match->homeTeam->logo)
                    : null,
            ],
            'away_team' => [
                'id' => $match->awayTeam->id,
                'name' => $match->awayTeam->name,
                'logo_url' => $match->awayTeam->logo
                    ? asset('storage/' . $match->awayTeam->logo)
                    : null,
            ],
            'match_date' => $match->match_date,
            'stadium' => $match->stadium,
            'status' => $match->status,
        ]);
    }

    //==================
    // POST /api/matches
    //==================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'home_team_id' => ['required', 'exists:teams,id'],
            'away_team_id' => ['required', 'exists:teams,id', 'different:home_team_id'],
            'home_score'   => ['nullable', 'integer', 'min:0'],
            'away_score'   => ['nullable', 'integer', 'min:0'],
            'match_date'   => ['required', 'date'],
            'status'       => ['required', Rule::in(['upcoming', 'history'])],
        ]);

        $homeTeam = Team::findOrFail($validated['home_team_id']);
        $validated['stadium'] = $homeTeam->stadium;

        if ($validated['status'] === 'history') {
            $validated['home_score'] ??= 0;
            $validated['away_score'] ??= 0;
        } else {
            $validated['home_score'] = null;
            $validated['away_score'] = null;
        }

        $match = MatchGame::create($validated);

        return response()->json([
            'success' => true,
            'data' => $match->load(['homeTeam', 'awayTeam']),
            'message' => 'Match berhasil dibuat'
        ], 201);
    }

    //======================
    // GET /api/matches/{id}
    //======================
    public function show($id)
    {
        $match = MatchGame::with([
            'homeTeam',
            'awayTeam',
            'lineups.player',
            'lineups.team',
            'stats',
            'overviews.player'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $match
        ]);
    }

    //======================
    // PUT /api/matches/{id}
    //======================
    public function update(Request $request, $id)
    {
        $match = MatchGame::findOrFail($id);

        $validated = $request->validate([
            'home_team_id' => ['required', 'exists:teams,id'],
            'away_team_id' => ['required', 'exists:teams,id', 'different:home_team_id'],
            'home_score'   => ['nullable', 'integer', 'min:0'],
            'away_score'   => ['nullable', 'integer', 'min:0'],
            'match_date'   => ['required', 'date'],
            'status'       => ['required', Rule::in(['upcoming', 'history'])],
            
            
            'home_possession' => ['nullable', 'integer', 'min:0', 'max:100'],
            'away_possession' => ['nullable', 'integer', 'min:0', 'max:100'],
            'home_shots' => ['nullable', 'integer', 'min:0'],
            'away_shots' => ['nullable', 'integer', 'min:0'],
            'home_fouls' => ['nullable', 'integer', 'min:0'],
            'away_fouls' => ['nullable', 'integer', 'min:0'],
            'home_corner' => ['nullable', 'integer', 'min:0'],
            'away_corner' => ['nullable', 'integer', 'min:0'],
        ]);

        
        $homeTeam = Team::findOrFail($validated['home_team_id']);
        
        
        $matchData = [
            'home_team_id' => $validated['home_team_id'],
            'away_team_id' => $validated['away_team_id'],
            'match_date' => $validated['match_date'],
            'status' => $validated['status'],
            'stadium' => $homeTeam->stadium,
        ];

        
        if ($validated['status'] === 'history') {
            $matchData['home_score'] = $validated['home_score'] ?? 0;
            $matchData['away_score'] = $validated['away_score'] ?? 0;
        } else {
            $matchData['home_score'] = null;
            $matchData['away_score'] = null;
        }

        $match->update($matchData);

        
        if (
            isset($validated['home_possession']) || 
            isset($validated['home_shots']) ||
            isset($validated['home_fouls']) ||
            isset($validated['home_corner'])
        ) {
            
            if (
                isset($validated['home_possession']) && 
                isset($validated['away_possession']) &&
                $validated['home_possession'] + $validated['away_possession'] !== 100
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total possession harus 100%',
                ], 422);
            }

            $statsData = [
                'match_id' => $match->id,
                'home_possession' => $validated['home_possession'] ?? null,
                'away_possession' => $validated['away_possession'] ?? null,
                'home_shots' => $validated['home_shots'] ?? null,
                'away_shots' => $validated['away_shots'] ?? null,
                'home_fouls' => $validated['home_fouls'] ?? null,
                'away_fouls' => $validated['away_fouls'] ?? null,
                'home_corner' => $validated['home_corner'] ?? null,
                'away_corner' => $validated['away_corner'] ?? null,
            ];

            Stats::updateOrCreate(
                ['match_id' => $match->id],
                $statsData
            );
        }

        return response()->json([
            'success' => true,
            'data' => $match->load(['homeTeam', 'awayTeam', 'stats']),
            'message' => 'Match berhasil diperbarui'
        ]);
    }

    //=========================
    // DELETE /api/matches/{id}
    //=========================
    public function destroy($id)
    {
        MatchGame::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Match berhasil dihapus'
        ]);
    }
}