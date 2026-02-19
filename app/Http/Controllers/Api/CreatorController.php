<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Creator;
use Illuminate\Support\Facades\Storage;

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
}
