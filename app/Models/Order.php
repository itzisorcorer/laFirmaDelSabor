<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Order extends Model
{
    //
    use HasFactory, HasApiTokens;
    protected $primaryKey = 'order_id';
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];

    //pertenece a un usuario (coomprador)
    public function buyer(){
        return $this->belongsTo(User::class, 'user_id');
    }

    //un pedido tiene muchos items
    public function items(){
        return $this->hasMany(OrderItem::class);
    }
}
