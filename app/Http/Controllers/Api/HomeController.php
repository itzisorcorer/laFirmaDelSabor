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
        // Identificar quién está pidiendo los datos (gracias al Token)
        $user = $request->user(); 

        // Obtener todas las categorías
        $categories = Category::select('category_id', 'name', 'image_url')->get();

        //Saber qué productos tiene en favoritos este usuario
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('product_id')->toArray();

        // Agregados recientemente
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
                    'rating' => '4.5',
                    'image_url' => ProductImage::where('product_id', $product->product_id)
                    ->where('is_primary', true)->value('image_url'),
                    //si el ID está en sus favoritos, devuelve true
                    'is_favorite' => in_array($product->product_id, $favoriteIds),
                ];
            });
        // Últimos vistos (Del historial de este usuario)
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
            ];});
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
                ];}}
        // Empaquetar todo y enviarlo a Flutter
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
