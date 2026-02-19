<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class UserHistory extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'user_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'product_id',
        'viewed_at'
    ];
}
