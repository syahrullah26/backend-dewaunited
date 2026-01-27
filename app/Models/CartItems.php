<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItems extends Model
{
    use HasFactory;
    
    protected $table = "cart_items";

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_stock_id',
        'quantity',
    ];

    public function cart() 
    {
        return $this->belongsTo(Cart::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function productStock()
    {
        return $this->belongsTo(ProductStock::class, 'product_stock_id');
    }
}