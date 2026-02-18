<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Creator extends Model
{
    use HasFactory, HasApiTokens;
    protected $primaryKey = 'creator_id';
    protected $fillable = [
        'name',
        'biography',
        'location',
        'photo_url',
        'cover_photo_url',
        'rating_avg'
    ];
}
