<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lookbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookbookController extends Controller
{
    public function index()
    {
        return Lookbook::with('products')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:lookbooks,slug|max:255',
            'hero_image' => 'nullable|string|url',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id'
        ]);

        return DB::transaction(function () use ($validated) {
            $lookbook = Lookbook::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                'hero_image' => $validated['hero_image'] ?? null
            ]);

            if (!empty($validated['products'])) {
                $lookbook->products()->sync($validated['products']);
            }

            return $lookbook->load('products');
        });
    }

    public function show($slugOrId)
    {
        $lookbook = Lookbook::with('products')
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        return $lookbook;
    }

    public function update(Request $request, $id)
    {
        $lookbook = Lookbook::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:lookbooks,slug,' . $id,
            'hero_image' => 'nullable|string|url',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id'
        ]);

        return DB::transaction(function () use ($validated, $lookbook) {
            $updateData = [
                'name' => $validated['name'],
                'hero_image' => $validated['hero_image'] ?? null
            ];

            if (isset($validated['slug'])) {
                $updateData['slug'] = $validated['slug'];
            } elseif ($validated['name'] !== $lookbook->name) {
                $updateData['slug'] = Str::slug($validated['name']);
            }

            $lookbook->update($updateData);

            if (isset($validated['products'])) {
                $lookbook->products()->sync($validated['products']);
            }

            return $lookbook->load('products');
        });
    }

    public function destroy($id)
    {
        $lookbook = Lookbook::findOrFail($id);
        $lookbook->delete();
        
        return response()->noContent();
    }
}