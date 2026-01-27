<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lookbook extends Model
{
    protected $fillable = ['name', 'slug', 'hero_image'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($lookbook) {
            if (empty($lookbook->slug)) {
                $lookbook->slug = Str::slug($lookbook->name);
            }
        });
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'lookbook_product',      
            'lookbook_id',           
            'product_id'             
        )->withTimestamps();
    }
}