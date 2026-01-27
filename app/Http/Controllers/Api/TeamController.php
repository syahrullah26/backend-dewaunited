<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    // ==================
    // GET /api/team
    // ==================
    public function index()
    {
        $teams = Team::latest()->paginate(6);
        
        $teams->getCollection()->transform(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'stadium' => $team->stadium,
                'logo' => $team->logo,  
                'logo_url' => $team->logo
                    ? url('storage/' . $team->logo)  
                    : null,
            ];
        });

        return response()->json($teams);
    }

    // ==================
    // POST /api/team
    // ==================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'stadium' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('teams', 'public');
            $validated['logo'] = $path;
        }

        $team = Team::create($validated);

        $team->logo_url = $team->logo ? url('storage/' . $team->logo) : null;

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => 'Team berhasil dibuat',
        ], 201);
    }

    // ==================
    // GET /api/team/{id}
    // ==================
    public function show(string $id)
    {
        $team = Team::findOrFail($id);
        
        $team->logo_url = $team->logo ? url('storage/' . $team->logo) : null;

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }

    // ==================
    // PUT /api/team/{id}
    // ==================
    public function update(Request $request, string $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'stadium' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            
            if ($team->logo && Storage::disk('public')->exists($team->logo)) {
                Storage::disk('public')->delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')->store('teams', 'public');
        }

        $team->update($validated);

        $team->logo_url = $team->logo ? url('storage/' . $team->logo) : null;

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => 'Team sukses diperbarui',
        ]);
    }

    // ==================
    // DELETE /api/team/{id}
    // ==================
    public function destroy(string $id)
    {
        $team = Team::findOrFail($id);

        if ($team->logo && Storage::disk('public')->exists($team->logo)) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team berhasil dihapus',
        ]);
    }
}