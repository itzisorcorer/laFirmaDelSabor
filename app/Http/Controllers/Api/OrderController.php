<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;


class OrderController extends Controller
{
public function checkout(Request $request)
    {
        //  Validamos que lleguen los datos desde Flutter
        $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric'
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();

            //  Creamos la orden principal usando TUS nombres de columnas
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'assigned_admin_id' => null, // Se queda nulo hasta que el gestor lo asigne
                'total_amount' => $request->total,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'order_id');

            //  Guardamos el detalle mapeando los nombres de Flutter a tu BD
            $orderItems = [];
            foreach ($request->items as $item) {
                //Buscamos el producto y validamos stock
                $product = Product::find($item['product_id']);
                if(!$product){
                    throw new Exception("El producto ID {$item['product_id']} no existe.");
                }
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Stock insuficiente para el producto: " . $product->name);
                }
                
                // Descontamos el stock y guardamos
                $product->stock -= $item['quantity'];
                $product->save();
                

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
    public function getMyOrders(Request $request)
    {
        $user = $request->user();

        // Traemos todas las órdenes del usuario
        $orders = DB::table('orders')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $orderIds = $orders->map(function($o) { return $o->order_id ?? $o->id; })->toArray();

        //  Traemos todos los items y sus fotos
        $allItems = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->leftJoin('product_images', function($join) {
                $join->on('products.product_id', '=', 'product_images.product_id')
                     ->where('product_images.is_primary', true); // Solo la portada
            })->leftJoin('reviews', function($join) use ($user){
                $join->on('products.product_id', '=', 'reviews.product_id')
                     ->where('reviews.user_id', $user->id); // Solo la reseña del usuario logueado
            })
            ->whereIn('order_items.order_id', $orderIds)
            ->select(
                'order_items.order_id',
                'order_items.product_id',
                'order_items.amount_item', 
                'order_items.purchase_price', 
                'products.name', 
                'product_images.image_url as main_image_url',
                'reviews.review_id'
            )
            ->get();
        //  Agrupamos los items
        $itemsByOrder = $allItems->groupBy('order_id');

        // Armamos el paquete (el arbol json)
        $formattedOrders = $orders->map(function ($order) use ($itemsByOrder) {
            $orderId = $order->order_id ?? $order->id;
            
            // Transformamos los items para mandar un booleano limpio a Flutter
            $myItems = $itemsByOrder->get($orderId, collect())->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'amount_item' => $item->amount_item,
                    'purchase_price' => $item->purchase_price,
                    'name' => $item->name,
                    'main_image_url' => $item->main_image_url,
                    'has_reviewed' => $item->review_id != null 
                ];
            })->toArray();
            return [
                'order_id' => $orderId,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'date' => $order->created_at ? Carbon::parse($order->created_at)->format('d/m/Y') : 'Fecha desconocida',
                'items' => $myItems 
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $formattedOrders
        ]);
    }
}
