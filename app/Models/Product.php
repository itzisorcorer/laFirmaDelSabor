<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
    //
    use HasApiTokens, HasFactory;
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'subcategory_id',
        'user_id',
        'creator_id',
        'name',
        'description',
        'price',
        'stock',
        'status',
        'main_image_url',
        'expiration_date',
        'accessibility_description',
    ];

    //pertenece a una subcategoria
    public function subcategory(){
        return $this->belongsTo(Subcategory::class);
    }

    //pertenece a usuario (el admin que lo sube)
    public function creator(){
        return $this->belongsTo(User::class, 'user_id');
    }

    //tiene muchos videos
    public function videos(){
        return $this->hasMany(ProductVideo::class);
    }
}
