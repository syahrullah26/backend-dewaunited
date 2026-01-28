<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'banner_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta_keywords' => 'array',
        'view_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'draft',
        'view_count' => 0,
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' 
               && $this->published_at !== null 
               && $this->published_at->lte(now());
    }
}