<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\UserHistory;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\HttpCache\Store;

class ProductController extends Controller
{
    // LISTAR PRODUCTOS (API Pública)
    // GET /api/products
    public function index()
    {
        // Traemos los productos activos con sus relaciones
        $products = Product::with(['subcategory.category', 'creator', 'videos', 'images'])
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
        // 1. Validar los datos
        $validated = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,subcategory_id',
            'creator_id' => 'required|exists:creators,creator_id', 
            'name' => 'required|string|max:50', 
            'description' => 'required|string', 
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'accessibility_description' => 'required|string',
            'expiration_date' => 'nullable|date',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'videos' => 'nullable|array',
            'videos.*' => 'url',
        ]);



        //Registrar en la base de datos
        $product = Product::create([
            'subcategory_id' => $validated['subcategory_id'],
            'creator_id' => $validated['creator_id'],
            'user_id' => $request->user()->id, // El Admin que lo subió
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'accessibility_description' => $validated['accessibility_description'],
            'expiration_date' => $validated['expiration_date'] ?? null,
            'status' => $validated['status'],
        ]);
        // Guardar las imágenes relacionadas al producto
        if($request->hasFile('images')){
            $isFirst = true;
            foreach($request->file('images') as $image){
                $imagePath = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_url' => $imagePath,
                    'is_primary' => $isFirst, // La primera imagen es la principal
                ]);
                $isFirst = false; // Solo la primera imagen será la principal

            }

        }
        // Guardar los videos relacionados al producto
        if($request->has('videos')){
            foreach($request->videos as $videoUrl){
                if(!empty($videoUrl)){
                    DB::table('product_videos')->insert([
                        'product_id' => $product->product_id,
                        'url_youtube' => $videoUrl,
                        'accessibility_description' => 'Video relacionado al producto ' . $product->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

        }

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data' => $product->load('images')
        ], 201);
    }
    //VER LOS DETALLES DE UN PRODUCTO ESPECIFICO
    // GET /api/products/{id}
    public function show(Request $request, $id){
        $product = Product::with(['creator', 'subcategory.category', 'images'])->find($id);
        //obtener los videos relacionados al producto
        $videos = DB::table('product_videos')->where('product_id', $id)->get();

        $product->videos = $videos;

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
                'is_favorite' => $isFavorite,
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
        $products = Product::with('images')->where('name', 'ilike', '%' . $query . '%')
        ->where('status', 1)->get();
        $user = $request->user();

        $formattedProducts = $products->map(function($product) use ($user){
            $isFavorite = $user ? Favorite::where('user_id', $user->id)->where('product_id', $product->product_id)->exists() : false;

            $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
            return [
                'product_id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => '$' . number_format($product->price, 2) . ' c/u',
                'image_url' => $primaryImage ? $primaryImage->image_url : null,
                'rating' => '4.5',
                'is_favorite' => $isFavorite
            ];

        });
        return response()->json([
            'success' => true,
            'data' => $formattedProducts
        ]);

    }
    public function getByCategory(Request $request, $id){
        //Obtenemos las categorias que pertenecen a esta caegoria padre
        $subcategories = DB::table('subcategories')
        ->where('category_id', $id)->pluck('subcategory_id');

        //buscamos los productos que tengamos alguna de esas subcategorias
        $products = Product::with('images')->whereIn('subcategory_id', $subcategories)->where('status', 1)->get();

        $user = $request->user();

        //formato
        $formattedProducts = $products->map(function ($product) use ($user){
            $isFavorite = $user ? Favorite::where('user_id', $user->id)
            ->where('product_id', $product->product_id)->exists() : false;

            $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();

            return[
                'product_id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => '$' . number_format($product->price, 2) . ' c/u',
                'image_url' => $primaryImage ? $primaryImage->image_url : null,
                'rating' => '4.5',
                'is_favorite' => $isFavorite
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $formattedProducts
        ]);

    }
    // Actualizar producto (Admin)
    //POST /api/products/{id}
    public function update(Request $request, $id){
        $product = Product::findOrFail($id);

        //validar datos antes de insertar
        $validated = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,subcategory_id',
            'creator_id' => 'required|exists:creators,creator_id',
            'name' => 'required|string|max:50',
            'description' => 'required|string',
            'price' => 'required|numeric|min:1',
            'stock' => 'required|integer|min:1',
            'status' => 'required|boolean',
            'accessibility_description' => 'required|string',
            'expiration_date' => 'nullable|date',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,png,jpeg,gif|max:3072',
            'videos' => 'nullable|array',
            'videos.*' => 'url',
            //listas para eliminar fotos o videos existentes
            'deleted_images' => 'nullable|array',
            'deleted_images.*'=>'string',
            'deleted_videos'=>'nullable|array',
            'deleted_videos.*'=>'string'
        ]);
        //actualizamos los datos de la base
        $product->update([
            'subcategory_id' => $validated['subcategory_id'],
            'creator_id' => $validated['creator_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'accessibility_description' => $validated['accessibility_description'],
            'expiration_date' => $validated['expiration_date'] ?? null,
            'status' => $validated['status'],
        ]);

        //agregamos nuevas fotos por si se añadieron
        if($request->hasFile('images')){
            $hasPrimary = ProductImage::where('product_id', $product->product_id)
            ->where('is_primary', true)->exists();

            //si no tiene foto de portada, la primera será
            $isFirst = !$hasPrimary;

            foreach($request->file('images') as $image){
                $imagePath = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_url' => $imagePath,
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;

            }
        }
        //agregamos nuevos videos por si se añadieron
        if($request->has('videos')){
            foreach($request->videos as $videoUrl){
                if(!empty($videoUrl)){
                    DB::table('product_videos')->insert([
                        'product_id' => $product->product_id,
                        'url_youtube' => $videoUrl,
                        'accessibility_description' => 'Video relacionado al producto ' . $product->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                }

            }

        }
        //eliminar las fotos que se pidieron eliminar
        if($request->has('deleted_images')){
            foreach($request->deleted_images as $imgUrl){
                ProductImage::where('product_id', $product->product_id)
                ->where('image_url', $imgUrl)->delete();
                //eliminar el archivo físico del disco
                Storage::disk('public')->delete($imgUrl);
            }
        }
        //eliminar los videos que se pidieron eliminar
        if($request->has('deleted_videos')){
            foreach($request->deleted_videos as $videoUrl){
                DB::table('product_videos')->where('product_id', $product->product_id)
                ->where('url_youtube', $videoUrl)->delete();
            }

        }
        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente'
        ]);
    }

}
