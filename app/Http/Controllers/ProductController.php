<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //LISTAR PRODUCTOS (api pública)
    //GET /api/products
    public function index(){
        $products = Product::with(['subcategory.category', 'creator', 'videos'])
        ->where('status', true)->get();

        return response()->json($products);
    }

    //CREAR PRODUCTO (Solo de admin)
    //POST /api/products
    public function store(Request $request){
        //validar los productos
        $validated = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|string|max:70',
            'description' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'accessibility_description' => 'required|string',
        ]);

        //Manejar la subida de la imagen
        $imagePath = null;
        if($request->hasFile('image')){
            //se guardará en storage/app/public/products y devuelve la ruta
            $imagePath = $request->file('image')->store('products', 'public');
        }

        //Registrar en la base de datos
        $product =Product::create([
            'subcategory_id' => $validated['subcategory_id'],
            'user_id' => $request->user()->id, //para asignar automáticamente al usuario logueado
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'main_image_url' => $imagePath,
            'accessibility_description' => $validated['accessibility_description'],
            'status' => true,
        ]);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'product' => $product
        ], 201
        );

    }


}
