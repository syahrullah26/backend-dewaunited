<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UploadStorage extends Model
{
    use HasFactory;
    protected $table ='upload_storage';
    
    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'type',
        'product_id'
    ];
    
    public function product()
{
    return $this->belongsTo(Product::class,'product_id');
}

}
