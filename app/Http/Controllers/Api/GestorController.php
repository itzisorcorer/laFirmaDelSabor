<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

 

        return response()->json([
            'success' => true,
            'data' => $orders
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
