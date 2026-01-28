<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Activations extends Model
{
    use HasFactory;
    protected $table = 'activations';
    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'cover_image',
        'gallery',
        'location',
        'start_date',
        'end_date'
    ];
    protected $casts = [
        'gallery' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activation) {
            if (empty($activation->slug)) {
                $slug = Str::slug($activation->title);
                $count = static::where('slug', 'LIKE', "{$slug}%")->count();
                $activation->slug = $count ? "{$slug}-{$count}" : $slug;
            }
        });
    }
}
