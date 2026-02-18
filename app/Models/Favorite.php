<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Favorite extends Model
{
    use HasFactory, HasApiTokens;
    protected $primaryKey = 'favorite_id';
    protected $fillable = [
        'user_id',
        'product_id'
    ];
}
