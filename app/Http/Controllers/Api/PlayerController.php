<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;

class PlayerController extends Controller
{
    public function index()
    {    
        return response()->json([
            'success' => true,
            'data' => Player::all(),
        ]);
    }


    public function getByTeam($teamId)
    {
        $players = Player::where('team_id', $teamId)->get();

        return response()->json([
            'success' => true,
            'data' => $players
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'nama_pemain' => 'required|string|max:255',
            'posisi' => 'required|string|max:255',
        ]);

        $player = Player::create($validated);

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Player berhasil ditambahkan',
        ], 201);
    }

    public function playersByTeam($team_id)
    {
        $players = Player::where('team_id', $team_id)->get();
        return response()->json([
            'success' => true,
            'data' => $players
        ]);
    }

    public function show($id)
    {
        $player = Player::findOrFail($id);
        return response()->json(['success' => true, 'data' => $player]);
    }

    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);
        $validated = $request->validate([
            'nama_pemain' => 'required|string|max:255',
            'posisi' => 'required|string|max:255',
        ]);

        $player->update($validated);

        return response()->json(['success' => true, 'data' => $player, 'message' => 'Player berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return response()->json(['success' => true, 'message' => 'Player berhasil dihapus']);
    }
}
