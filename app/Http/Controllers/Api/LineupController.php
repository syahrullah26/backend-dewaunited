<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lineup;
use Illuminate\Support\Facades\DB;

class LineupController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Lineup::with(['match', 'team', 'player'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'match_id' => 'required|exists:matches,id',
            'team_id' => 'required|exists:teams,id',
            'player_id' => 'required|exists:players,id',
            'status' => 'required|in:starter,substitute',
        ]);

        $lineup = Lineup::create($validated);

        return response()->json([
            'success' => true,
            'data' => $lineup->load(['match', 'team', 'player']),
            'message' => 'Lineup berhasil ditambahkan',
        ], 201);
    }

    public function show($id)
    {
        $lineup = Lineup::with(['match', 'team', 'player'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $lineup,
        ]);
    }

    public function getByMatch($matchId)
    {
        $lineups = Lineup::where('match_id', $matchId)
            ->with(['team', 'player'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lineups,
        ]);
    }

    
    public function deleteByMatch($matchId)
    {
        Lineup::where('match_id', $matchId)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Lineup berhasil dihapus'
        ]);
    }

    public function update(Request $request, $id)
    {
        $lineup = Lineup::findOrFail($id);

        $validated = $request->validate([
            'team_id' => 'sometimes|exists:teams,id',
            'player_id' => 'sometimes|exists:players,id',
            'status' => 'sometimes|in:starter,substitute',
        ]);

        $lineup->update($validated);

        return response()->json([
            'success' => true,
            'data' => $lineup->load(['match', 'team', 'player']),
            'message' => 'Lineup berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $lineup = Lineup::findOrFail($id);
        $lineup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lineup berhasil dihapus',
        ]);
    }
}