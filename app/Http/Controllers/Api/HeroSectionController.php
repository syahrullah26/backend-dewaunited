<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HeroSection;
use Illuminate\Validation\Rule;

class HeroSectionController extends Controller
{
    public function index()
    {
        $hero = HeroSection::latest()->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => $hero,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'path_image' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $hero = HeroSection::create($validated);

        return response()->json([
            'success' => true,
            'data' => $hero,
            'message' => 'Hero Section berhasil dibuat',
        ], 201);
    }

    public function toggleActive($id)
    {
        $hero = HeroSection::findOrFail($id);
        if (!$hero->is_active) {

            $activeCount = HeroSection::where('is_active', true)->count();

            if ($activeCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 5 active hero sections allowed',
                ], 422);
            }

            $hero->update(['is_active' => true]);
        } else {
            $hero->update(['is_active' => false]);
        }

        return response()->json([
            'success' => true,
            'data' => $hero,
            'message' => 'Hero status updated',
        ]);
    }
}
