<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overview;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Overview::with(['match', 'player'])->get(),
        ]);
    }

    public function storeByMatch(Request $request, $matchId)
    {
        $validated = $request->validate([
            'homeGoals' => 'nullable|array',
            'homeGoals.*.playerId' => 'required|exists:players,id',
            'homeGoals.*.time' => 'required|integer|min:0|max:120',
            'awayGoals' => 'nullable|array',
            'awayGoals.*.playerId' => 'required|exists:players,id',
            'awayGoals.*.time' => 'required|integer|min:0|max:120',
        ]);

        DB::beginTransaction();
        try {
            Overview::where('match_id', $matchId)->delete();

            $overviews = [];

            
            if (!empty($validated['homeGoals'])) {
                foreach ($validated['homeGoals'] as $goal) {
                    $overviews[] = Overview::create([
                        'match_id' => $matchId,
                        'player_id' => $goal['playerId'],
                        'time' => $goal['time'],
                        'team_type' => 'home',
                    ]);
                }
            }

            if (!empty($validated['awayGoals'])) {
                foreach ($validated['awayGoals'] as $goal) {
                    $overviews[] = Overview::create([
                        'match_id' => $matchId,
                        'player_id' => $goal['playerId'],
                        'time' => $goal['time'],
                        'team_type' => 'away',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $overviews,
                'message' => 'Overview berhasil disimpan',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan overview: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getByMatch($matchId)
    {
        $overviews = Overview::where('match_id', $matchId)
            ->with('player')
            ->orderBy('time')
            ->get();

        $homeGoals = $overviews->where('team_type', 'home')->map(function($item) {
            return [
                'playerId' => $item->player_id,
                'time' => $item->time,
                'playerName' => $item->player->nama_pemain ?? null,
            ];
        })->values();

        $awayGoals = $overviews->where('team_type', 'away')->map(function($item) {
            return [
                'playerId' => $item->player_id,
                'time' => $item->time,
                'playerName' => $item->player->nama_pemain ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'homeGoals' => $homeGoals,
                'awayGoals' => $awayGoals,
            ]
        ]);
    }

    public function show($id)
    {
        $overview = Overview::with(['match', 'player'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    public function destroy($id)
    {
        $overview = Overview::findOrFail($id);
        $overview->delete();

        return response()->json([
            'success' => true,
            'message' => 'Overview berhasil dihapus',
        ]);
    }
}