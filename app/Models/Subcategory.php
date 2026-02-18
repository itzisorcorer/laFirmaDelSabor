<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Subcategory extends Model
{
    //
    use HasApiTokens, HasFactory;
    protected $primaryKey = 'subcategory_id';
    protected $fillable = [
        'category_id',
        'name',
    ];

    //pertenece a una categoria
    public function category(){
        return $this->belongsTo(Category::class);
    }

    //tiene muchos productos
    public function products(){
        return $this->hasMany(Product::class);
    }

    
}
