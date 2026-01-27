<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\ProductStock;
use App\Models\CartItems;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with([
            'items.product',
            'items.productStock'
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'items' => [],
                'summary' => [
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'total_price' => 0,
                ]
            ]);
        }

        $items = $cart->items->map(function ($item) {
            $price = (float) $item->product->price;
            $subtotal = $price * $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->images[0] ?? null,
                'size' => optional($item->productStock)->size,
                'price' => $price,
                'quantity' => $item->quantity,
                'stock_available' => optional($item->productStock)->stock,
                'subtotal' => $subtotal,
            ];
        });

        return response()->json([
            'cart_id' => $cart->id,
            'items' => $items,
            'summary' => [
                'total_items' => $items->count(),
                'total_quantity' => $items->sum('quantity'),
                'total_price' => $items->sum('subtotal'),
            ]
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size'       => 'required|string',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id,
                'status'  => 'active',
            ]);

            $stock = ProductStock::reduceStockOrFail(
                $request->product_id,
                $request->size,
                $request->quantity
            );

            $existingItem = CartItems::where('cart_id', $cart->id)
                ->where('product_stock_id', $stock->id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $request->quantity);
                $item = $existingItem;
            } else {
                $item = CartItems::create([
                    'cart_id' => $cart->id,
                    'product_id' => $stock->product_id,
                    'product_stock_id' => $stock->id,
                    'quantity' => $request->quantity,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil ditambahkan ke cart',
                'item' => $item->load(['product', 'productStock']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        
        $item = CartItems::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('status', 'active');
        })->findOrFail($itemId);

        DB::beginTransaction();
        try {
            $difference = $request->quantity - $item->quantity;
            
            if ($difference > 0) {
                ProductStock::reduceStockOrFail(
                    $item->product_id,
                    $item->productStock->size,
                    $difference
                );
            } else if ($difference < 0) {
                $item->productStock->increment('stock', abs($difference));
            }

            $item->update(['quantity' => $request->quantity]);
            
            DB::commit();

            return response()->json([
                'message' => 'Quantity berhasil diupdate',
                'item' => $item->fresh()->load(['product', 'productStock']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function removeItem($itemId)
    {
        $user = request()->user();
        
        $item = CartItems::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('status', 'active');
        })->findOrFail($itemId);

        DB::beginTransaction();
        try {
            $item->productStock->increment('stock', $item->quantity);
            
            $item->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Item berhasil dihapus dari cart',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function clear()
    {
        $user = request()->user();
        
        $cart = Cart::with('items.productStock')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart sudah kosong']);
        }

        DB::beginTransaction();
        try {
            foreach ($cart->items as $item) {
                $item->productStock->increment('stock', $item->quantity);
            }
            
            $cart->items()->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Cart berhasil dikosongkan',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}