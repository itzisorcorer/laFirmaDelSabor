<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class GestorController extends Controller
{
    //Obtener todas las órdenes (con info del cliente y admins)
    public function getAllOrders()
    {
        $orders = DB::table('orders')
            ->join('users as buyers', 'orders.user_id', '=', 'buyers.id') 
            ->leftJoin('users as admins', 'orders.assigned_admin_id', '=', 'admins.id')
            ->select(
                'orders.*', 
                'buyers.name as buyer_name',
                'admins.name as admin_name'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();
        $formattedOrders = $orders->map(function ($order) {
            $items = DB::table('order_items')->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->where('order_items.order_id', $order->order_id)
            ->select('order_items.amount_item', 'products.name')->get();

            return [
                'order_id' => $order->order_id,
                'status' => $order->status,
                'buyer_name' => $order->buyer_name,
                'admin_name' => $order->admin_name,
                'assigned_admin_id' => $order->assigned_admin_id,
                'created_at' => $order->created_at,
                'items' => $items
            ];
        });
 

        return response()->json([
            'success' => true,
            'data' => $formattedOrders
        ]);
    }

    // 2. Obtener lista de administradores disponibles para asignar
    public function getAvailableAdmins()
    {
        $admins = DB::table('users')->where('role', 'admin')->select('id', 'name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    // 3. Cambiar el estatus o asignar admin
    public function updateOrder(Request $request, $orderId)
    {
        $updateData = [];
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        
        if ($request->has('assigned_admin_id')) {
            $updateData['assigned_admin_id'] = $request->assigned_admin_id;
        }

        $updateData['updated_at'] = now();

        DB::table('orders')->where('order_id', $orderId)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizada correctamente'
        ]);
    }
}
