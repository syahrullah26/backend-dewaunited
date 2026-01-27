<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HeroSection extends Model
{
    use HasFactory;
    protected $table = 'hero_section';

    protected $fillable = [
        'path_image',
        'is_active',
    ];
}
