<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class OrderItem extends Model
{
    //
    use HasApiTokens, HasFactory;
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'product_id',
        'amount_item',
        'purchase_price',
    ];

    //pertenece a un pedido padre
    public function order(){
        return $this->belongsTo(Order::class);
    }

    //se refiere a un producto
    public function product(){  
        return $this->belongsTo(Product::class);
    }
}
