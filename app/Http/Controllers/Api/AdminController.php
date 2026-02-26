<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;

class AdminController extends Controller
{
    //obtener solo las ordenes asignadas a un admin
    public function getMyAssignedOrders(Request $request){
        $user = $request->user();

        $orders = DB::table('orders')->join('users as buyers', 'orders.user_id', '=', 'buyers.id')
        ->where('assigned_admin_id', $user->id)-> select('orders.*', 'buyers.name as buyer_name')
        ->orderBy('orders.updated_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    //metodo para que el admin pueda cambiar el estado a los ultimos 2
    public function updateOrderStatus(Request $request, $orderId){
        DB::table('orders')->where('order_id', $orderId)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);
        return response()->json([
            'success' => true
        ]);
    }
}
