<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
    //OBTENER MIS PEDIDOS SEGUN EL USUARIO LOGUEADO
    //GET /api/orders
    public function getMyOrders(Request $request){
        $user = $request->user();

        //traemos las ordenes
        $orders = DB::table('orders')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        //traemos los productos de cada orden
        $formattedOrders = $orders->map(function ($order){
            $items = DB::table('order_items')->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->where('order_items.order_id', $order->order_id)
            ->select('order_items.amount_item', 'order_items.purchase_price', 'products.name', 'products.main_image_url')->get();

            return[
                'order_id' => $order->order_id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'date' => Carbon::parse($order->created_at)->format('d/m/Y'),
                'items' => $items
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $formattedOrders
        ]);
    }
}
