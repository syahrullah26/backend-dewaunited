<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('stocks');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'video_url' => $product->video_url,
                'images' => $product->images,
                'colors' => $product->colors,
                'stocks' => $product->stocks,
                'total_stock' => $product->total_stock,
                'badges' => $product->badges,
                'description' => $product->description,
                'size_guide_desc' => $product->size_guide_desc,
                'shipping_info' => $product->shipping_info,
                'trust_badges' => $product->trust_badges,
                'size_guide' => $product->size_guide,
                'description_video_url' => $product->description_video_url,
                'detail_product' => $product->detail_product,
                'detail_images' => $product->detail_images,
                'lifestyle_images' => $product->lifestyle_images,
                'related_products' => $product->relatedProducts()->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => $p->price,
                    'image' => $p->images[0] ?? null,
                ]),

                'external_links' => $product->external_links,
                'shopee_link' => $product->shopee_link,
                'tokopedia_link' => $product->tokopedia_link,
                'tiktok_shop_link' => $product->tiktok_shop_link,
                
                'is_active' => $product->is_active,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ]);

    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'video_url' => 'nullable|url',

            'images' => 'required|array|min:1',
            'images.*' => 'url',

            'colors' => 'required|array|min:1',
            'colors.*.name' => 'required|string',
            'colors.*.hex' => 'required|string',
            'colors.*.images' => 'required|array',
            'colors.*.images.*' => 'url',

            'stocks' => 'required|array|min:1',
            'stocks.*.size' => 'required|string|max:10',
            'stocks.*.stock' => 'required|integer|min:0',

            'badges' => 'required|array|min:1',
            'badges.*.icon' => 'required|string',
            'badges.*.text' => 'required|string',

            'description' => 'required|string',
            'size_guide_desc' => 'required|string',

            'shipping_info' => 'required|array',
            'shipping_info.*.tier' => 'required|string',
            'shipping_info.*.price' => 'required|numeric|min:0',
            'shipping_info.*.days' => 'required|string',

            'trust_badges' => 'required|array',
            'trust_badges.*.icon' => 'required|string',
            'trust_badges.*.text' => 'required|string',

            'size_guide' => 'required|array',

            'description_video_url' => 'nullable|url',
            'detail_product' => 'nullable|string',

            'detail_images' => 'nullable|array',
            'detail_images.*.url' => 'required|url',
            'detail_images.*.caption' => 'required|string',

            'lifestyle_images' => 'nullable|array',
            'lifestyle_images.*' => 'url',

            'related_products' => 'nullable|array',
            'related_products.*' => 'integer|exists:products,id',

            'external_links' => 'nullable|array',
            'external_links.shopee' => 'nullable|url',
            'external_links.tokopedia' => 'nullable|url',
            'external_links.tiktok_shop' => 'nullable|url',

            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $originalSlug = $validated['slug'];
        $count = 1;

        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$originalSlug}-{$count}";
            $count++;
        }

        $product = Product::create(
            collect($validated)->except('stocks')->toArray()
        );

        foreach ($validated['stocks'] as $stock) {
            $product->stocks()->create([
                'size' => strtoupper($stock['size']),
                'stock' => $stock['stock'],
            ]);
        }

        $product->update([
            'is_active' => $product->total_stock > 0
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product->load('stocks')
        ], 201);
    }


    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'video_url' => 'nullable|url',

            'images' => 'sometimes|required|array|min:1',
            'images.*' => 'url',

            'colors' => 'sometimes|required|array|min:1',
            'colors.*.name' => 'required|string',
            'colors.*.hex' => 'required|string',
            'colors.*.images' => 'required|array',
            'colors.*.images.*' => 'url',

            'stocks' => 'sometimes|required|array|min:1',
            'stocks.*.size' => 'required|string|max:10',
            'stocks.*.stock' => 'required|integer|min:0',

            'badges' => 'sometimes|required|array|min:1',
            'badges.*.icon' => 'required|string',
            'badges.*.text' => 'required|string',

            'description' => 'sometimes|required|string',
            'size_guide_desc' => 'sometimes|required|string',

            'shipping_info' => 'sometimes|required|array',
            'shipping_info.*.tier' => 'required|string',
            'shipping_info.*.price' => 'required|numeric|min:0',
            'shipping_info.*.days' => 'required|string',

            'trust_badges' => 'sometimes|required|array',
            'trust_badges.*.icon' => 'required|string',
            'trust_badges.*.text' => 'required|string',

            'size_guide' => 'sometimes|required|array',

            'description_video_url' => 'nullable|url',
            'detail_product' => 'nullable|string',

            'detail_images' => 'nullable|array',
            'detail_images.*.url' => 'required|url',
            'detail_images.*.caption' => 'required|string',

            'lifestyle_images' => 'nullable|array',
            'lifestyle_images.*' => 'url',

            'related_products' => 'nullable|array',
            'related_products.*' => 'integer|exists:products,id',

            'external_links' => 'nullable|array',
            'external_links.shopee' => 'nullable|url',
            'external_links.tokopedia' => 'nullable|url',
            'external_links.tiktok_shop' => 'nullable|url', 

            'is_active' => 'boolean',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $originalSlug = $validated['slug'];
            $count = 1;

            while (
                Product::where('slug', $validated['slug'])
                    ->where('id', '!=', $product->id)
                    ->exists()
            ) {
                $validated['slug'] = "{$originalSlug}-{$count}";
                $count++;
            }
        }

        if (isset($validated['stocks'])) {
            foreach ($validated['stocks'] as $stock) {
                $product->stocks()->updateOrCreate(
                    ['size' => strtoupper($stock['size'])],
                    ['stock' => $stock['stock']]
                );
            }
            
            $validated['is_active'] = $product->total_stock > 0;
        }

        $product->update(
            collect($validated)->except('stocks')->toArray()
        );

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product->load('stocks')
        ]);
    }


    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    public function toggleStatus(Product $product): JsonResponse
    {
        $product->update([
            'is_active' => !$product->is_active
        ]);

        return response()->json([
            'message' => 'Product status updated successfully',
            'data' => $product
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id'
        ]);

        Product::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Products deleted successfully'
        ]);
    }
}