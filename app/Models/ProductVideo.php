<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class ProductVideo extends Model
{
    //
    use HasApiTokens, HasFactory;
    protected $primaryKey = 'product_video_id';
    protected $fillable = [
        'product_id',
        'url_video',
        'accessibility_description',
    ];

    //pertenece a un producto
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
