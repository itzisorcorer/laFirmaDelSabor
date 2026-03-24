<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;
use App\Models\User;
use \Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //obtener solo las ordenes asignadas a un admin
    // Obtener SOLO las órdenes asignadas a este admin (con sus PRODUCTOS y DIRECCIÓN)
    public function getMyAssignedOrders(Request $request)
    {
        $user = $request->user();

        $orders = DB::table('orders')
            ->join('users as buyers', 'orders.user_id', '=', 'buyers.id')
            ->where('assigned_admin_id', $user->id)
            ->select(
                'orders.*', 
                'buyers.name as buyer_name',
                'buyers.street as buyer_street',
                'buyers.neighborhood as buyer_neighborhood',
                'buyers.city as buyer_city',
                'buyers.postal_code as buyer_postal_code',
                'buyers.phone_number as buyer_phone'
            )
            ->orderBy('orders.updated_at', 'desc')
            ->get();

        // Mapeamos para inyectarle los productos
        $formattedOrders = $orders->map(function ($order){
            $items = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.product_id')
                ->where('order_items.order_id', $order->order_id)
                ->select('order_items.amount_item', 'products.name')
                ->get();

            return [
                'order_id' => $order->order_id,
                'status' => $order->status,
                'buyer_name' => $order->buyer_name,
                'buyer_street' => $order->buyer_street,
                'buyer_neighborhood' => $order->buyer_neighborhood,
                'buyer_city' => $order->buyer_city,
                'buyer_postal_code' => $order->buyer_postal_code,
                'buyer_phone' => $order->buyer_phone,
                'items' => $items
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedOrders
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
    //OBTENER TODOS LOS PRODUCTOS SIN IMPORTAR EL STATUS
    //GET /api/admin/products
    public function getProducts(Request $request){
        $products = DB::table('products')->join('creators', 'products.creator_id', '=', 'creators.creator_id')
        ->join('subcategories', 'products.subcategory_id', '=', 'subcategories.subcategory_id')
        ->join('categories', 'subcategories.category_id', '=', 'categories.category_id')
        ->leftJoin('product_images', function($join){
            $join->on('products.product_id', '=', 'product_images.product_id')
                 ->where('product_images.is_primary', true);
        })
        ->select(
            'products.product_id',
            'products.name',
            'products.price',
            'products.stock',
            'products.status',
            'creators.name as creator_name',
            'subcategories.name as subcategory_name',
            'categories.category_id',
            'categories.name as category_name',
            'product_images.image_url as main_image_url'
            )->orderBy('products.created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
    }
    public function registerStaff(Request $request){
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:60|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,gestor'
        ]);

        // Creamos al nuevo administrador o gestor
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Personal registrado correctamente',
            'user' => $user
        ], 201);
    }
}
