<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Creator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CreatorController extends Controller
{
    // LISTAR CREADORAS (Para que el Admin elija al crear un producto)
    public function index()
    {
        $creators = Creator::all();
        
        return response()->json([
            'success' => true,
            'data' => $creators
        ]);
    }

    // CREAR NUEVA CREADORA (Solo Admin)
    public function store(Request $request)
    {
        // 1. Validar los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'biography' => 'required|string',
            'location' => 'required|string|max:255',
            // Las fotos son opcionales por si no tienen en el momento
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Subir Foto de Perfil (Si envió una)
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $request->file('photo')->store('creators/profiles', 'public');
        }

        // 3. Subir Foto de Portada (Si envió una)
        $coverPhotoUrl = null;
        if ($request->hasFile('cover_photo')) {
            $coverPhotoUrl = $request->file('cover_photo')->store('creators/covers', 'public');
        }

        // 4. Guardar en Base de Datos
        $creator = Creator::create([
            'name' => $validated['name'],
            'biography' => $validated['biography'],
            'location' => $validated['location'],
            'photo_url' => $photoUrl,
            'cover_photo_url' => $coverPhotoUrl,
            'rating_avg' => 0, // Inicia con 0 estrellas
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Creadora registrada exitosamente',
            'data' => $creator
        ], 201);
    }
    public function getProfile($id){
        $creator = DB::table('creators')->where('creator_id', $id)->first();

        if(!$creator){
            return response()->json([
                'success' => false,
                'message' => 'Creadora no encontrada'
            ], 404);
        }
        $products = DB::table('products')->where('creator_id', $id)->where('status', 1)->get();
        $productsIds = $products->pluck('product_id');

        $reviewsCount = DB::table('reviews')->whereIn('product_id', $productsIds)->count();

        $featuredReview = DB::table('reviews')->whereIn('product_id', $productsIds)
        ->orderBy('rating', 'desc')->orderBy('created_at', 'desc')->first();

        $bestRated = $products->take(5)->map(function ($p){
            $prodRating = DB::table('reviews')->where('product_id', $p->product_id)->avg('rating');

            return[
                'product_id' => $p->product_id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => $p->price,
                'main_image_url' => $p->main_image_url,
                'rating' => $prodRating ? round($prodRating, 1): 5.0

            ];
        });
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $creator->name,
                'specialty' => 'Artesano Local | ' . ($creator->location ?? 'México'),
                'about' => $creator->biography ?? 'Sin biografía disponible por el momento.',
                'profile_image' => $creator->photo_url ?? 'https://img.freepik.com/free-photo/portrait-young-woman-with-natural-make-up_23-2149084945.jpg',
                'background_image' => $creator->cover_photo_url ?? 'https://www.mexicodesconocido.com.mx/wp-content/uploads/2022/02/LP_Oaxaca-022-900x506.jpg',
                'rating' => $creator->rating_avg ?? 5.0, 
                'reviews_count' => $reviewsCount, 
                // Si hay reseña, la mandamos; si no, un texto bonito por defecto
                'featured_review' => $featuredReview ? '"' . $featuredReview->comment . '"' : '"Este creador aún no tiene reseñas."',
                'best_rated' => $bestRated
            ]
        ]);
    }
}
