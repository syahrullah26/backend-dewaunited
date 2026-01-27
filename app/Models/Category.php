<?php

namespace App\Models;

use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasCollection;
    protected $table = 'category';

    protected $fillable = [
        'name',
        'slug'
    ];
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
