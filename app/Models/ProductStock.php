<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductStock extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function reduceStockOrFail($productId, $size, $quantity)
    {
        $stock = self::where('product_id', $productId)
            ->where('size', $size)
            ->lockForUpdate() 
            ->first();

        if (!$stock) {
            throw new \Exception("Size $size tidak tersedia");
        }

        if ($stock->stock < $quantity) {
            throw new \Exception("Stock tidak mencukupi. Tersedia: {$stock->stock}");
        }

        $stock->decrement('stock', $quantity);
        
        return $stock->fresh();
    }
}
