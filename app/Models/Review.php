<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Review extends Model
{
    use HasFactory, HasApiTokens;
    protected $primaryKey = 'review_id';
    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment'
    ];
}
