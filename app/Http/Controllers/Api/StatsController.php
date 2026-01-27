<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stats;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Stats::with('match')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'match_id' => 'required|exists:matches,id',
            'home_possession' => 'nullable|integer|min:0|max:100',
            'away_possession' => 'nullable|integer|min:0|max:100',
            'home_shots' => 'nullable|integer|min:0',
            'away_shots' => 'nullable|integer|min:0',
            'home_fouls' => 'nullable|integer|min:0',
            'away_fouls' => 'nullable|integer|min:0',
            'home_corner' => 'nullable|integer|min:0',
            'away_corner' => 'nullable|integer|min:0',
        ]);

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

        $stats = Stats::updateOrCreate(
            ['match_id' => $validated['match_id']],
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Stats berhasil disimpan',
        ], 201);
    }

    public function show($id)
    {
        $stats = Stats::with('match')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getByMatch($matchId)
    {
        $stats = Stats::where('match_id', $matchId)->with('match')->first();

        if (!$stats) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getByMatchID($matchId)
    {
        $stats = Stats::where('match_id', $matchId)->first();

        if (!$stats) {
            return response()->json(null, 200);
        }

        return response()->json([
            [
                'label' => 'Possession',
                'home' => $stats->home_possession,
                'away' => $stats->away_possession,
                'isPercent' => true,
            ],
            [
                'label' => 'Shots',
                'home' => $stats->home_shots,
                'away' => $stats->away_shots,
                'isPercent' => false,
            ],
            [
                'label' => 'Fouls',
                'home' => $stats->home_fouls,
                'away' => $stats->away_fouls,
                'isPercent' => false,
            ],
            [
                'label' => 'Corner Kicks',
                'home' => $stats->home_corner,
                'away' => $stats->away_corner,
                'isPercent' => false,
            ],
        ]);
    }


    public function update(Request $request, $id)
    {
        $stats = Stats::findOrFail($id);

        $validated = $request->validate([
            'home_possession' => 'nullable|integer|min:0|max:100',
            'away_possession' => 'nullable|integer|min:0|max:100',
            'home_shots' => 'nullable|integer|min:0',
            'away_shots' => 'nullable|integer|min:0',
            'home_fouls' => 'nullable|integer|min:0',
            'away_fouls' => 'nullable|integer|min:0',
            'home_corner' => 'nullable|integer|min:0',
            'away_corner' => 'nullable|integer|min:0',
        ]);

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

        $stats->update($validated);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Stats berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $stats = Stats::findOrFail($id);
        $stats->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stats berhasil dihapus',
        ]);
    }
}
