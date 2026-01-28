<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivationController extends Controller
{
    public function index(Request $request)
    {
        $query = Activations::query();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $data = $query
            ->latest()
            ->paginate(9);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function show($slug)
    {
        $activation = Activations::where('slug', $slug)->firstOrFail();
        return response()->json([
            'status' => 'success',
            'data' => $activation
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:activations,slug',
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'cover_image' => 'required|string|max:255',
            'gallery' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);
        return DB::transaction(function () use ($request) {
            $activation = Activations::create([
                'title' => $request->title,
                'slug' => $request->slug ?? Str::slug($request->title),
                'category' => $request->category,
                'excerpt' => $request->excerpt,
                'content' => $request->content,
                'cover_image' => $request->cover_image,
                'gallery' => $request->gallery ?? null,
                'location' => $request->location ?? null,
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $activation
            ], 201);
        });
    }
    public function update(Request $request, $id)
    {
        $activation = Activations::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:activations,slug,' . $activation->id,
            'category' => 'sometimes|string|max:100',
            'excerpt' => 'sometimes|string',
            'content' => 'sometimes|string',
            'cover_image' => 'sometimes|string|max:255',
            'gallery' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $activation->update($request->only([
            'title',
            'slug',
            'category',
            'excerpt',
            'content',
            'cover_image',
            'gallery',
            'location',
            'start_date',
            'end_date',
        ]));

        return response()->json([
            'status' => 'success',
            'data' => $activation
        ]);
    }

    public function destroy($id)
    {
        $activation = Activations::findOrFail($id);
        $activation->delete();
        return response()->noContent();
    }
}
