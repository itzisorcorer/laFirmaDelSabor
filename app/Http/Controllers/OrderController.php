<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //CREAR PEDIDO (CHECKOUT)
    //post /api/orders

    public function store(Request $request){
        //validar que se reciba array de los productos
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
        //Iniciamos uan transacción a la base de datos (todo o nada)
        try{
            return DB::transaction(function () use ($request){
                //calcular el total real en DB
                $totalAmount = 0;
                $orderItemsData = [];
                foreach($request->products as $item){
                    $product = Product::findOrFail($item['id']);

                    //validar stock
                    if($product->stock < $item['quantity']){
                        throw new \Exception("No hay suficiente stock para: " . $product->name);
                    }
                    //calcular subtotal
                    $subTotal = $product->price * $item['quantity'];
                    $totalAmount += $subTotal;

                    //restamos al stock
                    $product->decrement('stock', $item['quantity']);

                    //preparamos el item
                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'amount_item' => $item['quantity'],
                        'purchase_price' => $product->price,
                    ];
                }
                //creamos la cabecera del pedido
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                ]);
                    
                    //Creamos los detalles del pedido
                    foreach($orderItemsData as $data){
                        //agreamos el id del pedido que acabamos de crear
                        $data['order_id'] = $order->id;
                        OrderItem::create($data);

                    }
                    return response()->json([
                        'message' => 'Pedido realizado con éxito',
                        'order_id' => $order->id,
                        'total_amount' => $totalAmount
                    ], 201);

                
            });
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ], 400);
        }
    }
    //VER MIS PEDIDOS
    //get /api/orders
    public function index(Request $request){
        $orders = Order::with('items.product') //trae los items y la info del producto
        ->where('user_id', $request->user()->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($orders);
    }
}
