<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\UserHistory;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // LISTAR PRODUCTOS (API Pública)
    // GET /api/products
    public function index()
    {
        // Traemos los productos activos con sus relaciones
        $products = Product::with(['subcategory.category', 'creator', 'videos'])
            ->where('status', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // CREAR PRODUCTO (Solo Admin)
    // POST /api/products
    public function store(Request $request)
    {
        // 1. Validar los datos con la nueva estructura ER
        $validated = $request->validate([
            // Ahora busca específicamente en la columna subcategory_id
            'subcategory_id' => 'required|exists:subcategories,subcategory_id',
            // NUEVO: Validar que la creadora exista
            'creator_id' => 'required|exists:creators,creator_id', 
            
            'name' => 'required|string|max:50', 
            'description' => 'required|string', 
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'accessibility_description' => 'required|string',
            
            'expiration_date' => 'nullable|date', 
        ]);

        // Manejar la subida de la imagen
        $imagePath = null;
        if($request->hasFile('image')){
            // Guarda en storage/app/public/products
            $imagePath = $request->file('image')->store('products', 'public');
        }

        //Registrar en la base de datos
        $product = Product::create([
            'subcategory_id' => $validated['subcategory_id'],
            'creator_id' => $validated['creator_id'],
            'user_id' => $request->user()->id, // El Admin que lo subió
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'main_image_url' => $imagePath,
            'accessibility_description' => $validated['accessibility_description'],
            'expiration_date' => $validated['expiration_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data' => $product
        ], 201);
    }
    //VER LOS DETALLES DE UN PRODUCTO ESPECIFICO
    // GET /api/products/{id}
    public function show(Request $request, $id){
        $product = Product::with(['creator', 'subcategory.category'])->find($id);

        if(!$product || !$product->status){
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado o inactivo'
            ], 404);
        }
        $user = $request->user();
        $isFavorite = false;

        if($user){
            $isFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $id)
            ->exists();

            UserHistory::updateOrCreate([
                'user_id' => $user->id, 'product_id' => $id],
                ['viewed_at' => now()]
                );
        }
        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'is_favorite' => $isFavorite
            ]
        ]);
    }
    // BUSCADOR DE PRODUCTOS
    // GET /api/search?q=termino
    public function search (Request $request){
        $query = $request->query('q');
        if(!$query){
            return response()->json([
                'success' => true,
                'data' => []

            ]);

        }
        //coincidencias con el nombre
        $products = Product::where('name', 'ilike', '%' . $query . '%')
        ->where('status', 1)->get();
        $user = $request->user();

        $formattedProducts = $products->map(function($product) use ($user){
            $isFavorite = $user ? Favorite::where('user_id', $user->id)->where('product_id', $product->product_id)->exists() : false;
            return [
                'product_id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => '$' . number_format($product->price, 2) . ' c/u',
                'image_url' => $product->main_image_url,
                'rating' => '4.5',
                'is_favorite' => $isFavorite
            ];

        });
        return response()->json([
            'success' => true,
            'data' => $formattedProducts
        ]);

    }
}
