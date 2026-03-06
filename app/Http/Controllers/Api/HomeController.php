<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\UserHistory;
use App\Models\ProductImage;
use League\CommonMark\Extension\Footnote\Event\FixOrphanedFootnotesAndRefsListener;


class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Identificar quién está pidiendo los datos (gracias al Token)
        $user = $request->user(); 

        // 2. Obtener todas las categorías (Las que sembramos con tu Seeder)
        $categories = Category::select('category_id', 'name', 'image_url')->get();

        // 3. Saber qué productos tiene en favoritos ESTE usuario
        // (Devuelve un arreglo simple con los IDs, ej: [1, 5, 12])
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('product_id')->toArray();

        // 4. "Agregados recientemente" (Los últimos 10 productos creados)
        $recentProducts = Product::where('status', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) use ($favoriteIds) {
                return [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => '$' . number_format($product->price, 2) . ' c/u', // Formateado
                    'rating' => '4.5', //Calcular promedio real de Reviews después
                    'image_url' => ProductImage::where('product_id', $product->product_id)
                    ->where('is_primary', true)->value('image_url'),
                    // Aquí ocurre la magia: si el ID está en sus favoritos, devuelve true
                    'is_favorite' => in_array($product->product_id, $favoriteIds),
                ];
            });

        // 5. "Últimos vistos" (Del historial de ESTE usuario)
        $historyRecords = UserHistory::where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->take(10)
            ->get();
        
        $recentlyViewed = [];
        //todos los productos
        $allProducts = Product::where('status', true)->inRandomOrder()->take(20)->get()
        ->map(function ($product) use ($favoriteIds) {
            return [
                'product_id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => '$' . number_format($product->price, 2) . ' c/u',
                'rating' => '4.5',
                'image_url' => ProductImage::where('product_id', $product->product_id)
                    ->where('is_primary', true)->value('image_url'),
                'is_favorite' => in_array($product->product_id, $favoriteIds),
            ];
        })
        ;

        foreach ($historyRecords as $record) {
            $product = Product::find($record->product_id);
            if ($product && $product->status) {
                $recentlyViewed[] = [
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => '$' . number_format($product->price, 2) . ' c/u',
                    'rating' => '4.5',
                    'image_url' => ProductImage::where('product_id', $product->product_id)
                    ->where('is_primary', true)->value('image_url'),
                    'is_favorite' => in_array($product->product_id, $favoriteIds),
                ];
            }
        }

        // 6. Empaquetar todo y enviarlo a Flutter
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'recent_products' => $recentProducts,
                'recently_viewed' => $recentlyViewed,
                'all_products' => $allProducts
            ]
        ]);
    }
    //OBTENER TODOS LOS FAVORITOS DEL USUARIO
    //GET /api/favorites
    public function getFavorites(Request $request)
    {
        $user = $request->user();
        
        //buscamos los ids
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('product_id')->toArray();

        //Traemos la información completa de esos favoritos
        $favorites = Product::whereIn('product_id', $favoriteIds)
        ->where('status', true)->get()
        ->map(function($product){
            return[
                'product_id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => '$' . number_format($product->price, 2) . ' c/u',
                'rating' => '4.5',
                'image_url' => ProductImage::where('product_id', $product->product_id)
                    ->where('is_primary', true)->value('image_url'),
                'is_favorite' => true, // Si está en favoritos, siempre es true
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);

    }
}
