<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
public function checkout(Request $request)
    {
        // 1. Validamos que lleguen los datos desde Flutter
        $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric'
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();

            // 2. Creamos la orden principal usando TUS nombres de columnas
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'assigned_admin_id' => null, // Se queda nulo hasta que el gestor lo asigne
                'total_amount' => $request->total,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'order_id');

            // 3. Guardamos el detalle mapeando los nombres de Flutter a tu BD
            $orderItems = [];
            foreach ($request->items as $item) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'amount_item' => $item['quantity'], // Cantidad del producto
                    'purchase_price' => $item['price'], // Precio congelado en la compra
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            DB::table('order_items')->insert($orderItems);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Orden creada con éxito!',
                'order_id' => $orderId
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
