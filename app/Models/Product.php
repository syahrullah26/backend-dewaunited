<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'price',
        'original_price',
        'video_url',
        'images',
        'colors',
        'sizes',
        'badges',
        'description',
        'size_guide_desc',
        'shipping_info',
        'trust_badges',
        'size_guide',
        'description_video_url',
        'detail_product',
        'detail_images',
        'lifestyle_images',
        'related_products',
        'is_active',
    ];


    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'images' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'badges' => 'array',
        'shipping_info' => 'array',
        'trust_badges' => 'array',
        'size_guide' => 'array',
        'detail_images' => 'array',
        'lifestyle_images' => 'array',
        'related_products' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function relatedProducts()
    {
        if (!$this->related_products) {
            return collect([]);
        }
        
        return static::whereIn('id', $this->related_products)
            ->where('is_active', true)
            ->get();
    }
    public function media()
    {
        return $this->hasMany(UploadStorage::class, 'product_id');
    }
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function getTotalStockAttribute()
    {
        if ($this->relationLoaded('stocks')){
            return $this->stocks->sum('stock');
        }
        return $this->stocks()->sum('stock');
    }


    public function lookbooks()
    {
        return $this->belongsToMany(
            Lookbook::class,
            'lookbook_product',      
            'product_id',            
            'lookbook_id'            
        )->withTimestamps();
    }
}