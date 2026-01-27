<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'user_address';

    protected $fillable = [
        'user_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address_detail',
        'postal_code',
        'recipient_name',
        'phone',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'province_id' => 'integer',
        'regency_id' => 'integer',
        'district_id' => 'integer',
        'village_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
