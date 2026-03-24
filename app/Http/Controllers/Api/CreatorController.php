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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'cv_file' => 'nullable|mimes:pdf,docx|max:5120'
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
        //Subir el archivo pdf del CV
        $cvUrl = null;
        if($request->hasFile('cv_file')){
            $cvUrl = $request->file('cv_file')->store('creators/cvs', 'public', );
        }

        // 4. Guardar en Base de Datos
        $creator = Creator::create([
            'name' => $validated['name'],
            'biography' => $validated['biography'],
            'location' => $validated['location'],
            'photo_url' => $photoUrl,
            'cover_photo_url' => $coverPhotoUrl,
            'cv_url' => $cvUrl,
            'rating_avg' => 0 // Inicia con 0 estrellas
            
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
            
            return response()->json(['success' => false, 'message' => 'Creadora no encontrada'], 404);
        }

        $products = DB::table('products')->where('creator_id', $id)->where('status', 1)->get();
        $productsIds = $products->pluck('product_id');

        $reviewsCount = DB::table('reviews')->whereIn('product_id', $productsIds)->count();
        $ratingAvg = DB::table('reviews')->whereIn('product_id', $productsIds)->avg('rating');

        
        $reviewsList = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->whereIn('reviews.product_id', $productsIds)
            ->select('reviews.rating', 'reviews.comment', 'users.name as user_name')
            ->orderBy('reviews.created_at', 'desc')
            ->take(5)
            ->get();

        $bestRated = $products->map(function ($p){
            $prodRating = DB::table('reviews')->where('product_id', $p->product_id)->avg('rating');
            $primaryImage = DB::table('product_images')->where('product_id', $p->product_id)->orderByDesc('is_primary')->first();
            
            return[
                'product_id' => $p->product_id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => $p->price,
                'main_image_url' => $primaryImage ? $primaryImage->image_url : 'https://img.freepik.com/free-photo/portrait-young-woman-with-natural-make-up_23-2149084945.jpg',
                'rating' => $prodRating ? round($prodRating, 1): 5.0
            ];
        })->sortByDesc('rating')->take(5)->values(); 

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $creator->name,
                'specialty' => 'Artesano Local | ' . ($creator->location ?? 'México'),
                'about' => $creator->biography ?? 'Sin biografía disponible por el momento.',
                'profile_image' => $creator->photo_url,
                'background_image' => $creator->cover_photo_url,
                'rating' => $ratingAvg ? number_format($ratingAvg, 1) : '5.0', 
                'reviews_count' => $reviewsCount, 
                
                'reviews' => $reviewsList,
                'best_rated' => $bestRated,
                'cv_url' => $creator->cv_url
            ]
        ]);
    }
    //ACTUALIZAR CREADORA (solo para admin)
    //POST /api/admin/creators/{id}
    public function update(Request $request, $id)
    {
        $creator = Creator::find($id);
        if(!$creator){
            return response()->json([
                'success' => false,
                'message' => 'Creadora no encontrada'
            ], 404);
        }
        $request -> validate([
            'name' => 'required|string|max:255',
            'biography' => 'required|string',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'cv_file' => 'nullable|mimes:pdf|max:5120',
        ]);
        //si se subieron fotos nuevas, se reemplazan
        if($request->hasFile('photo')){
            $creator->photo_url = $request->file('photo')->store('creators/profiles', 'public');
        }
        if($request->hasFile('cover_photo')){
            $creator->cover_photo_url = $request->file('cover_photo')->store('creators/covers', 'public');
        }
        if($request->hasFile('cv_file')){
            $creator->cv_url = $request->file('cv_file')->store('creators/cvs', 'public');
        }
        //actualizamos los demás campos
        $creator->name = $request->name;
        $creator->biography = $request->biography;
        $creator->location = $request->location;
        $creator->save();

        return response()->json([
            'success' => true,
            'message' => 'Creadora actualizada exitosamente',
            
        ]);
    }
}
