<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos que lleguen los datos correctos
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // 2. Revisamos si el usuario ya opinó sobre este producto antes (opcional, para evitar spam)
        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $request->product_id)
                                ->first();

        if ($existingReview) {
            return response()->json(['success' => false, 'message' => 'Ya has calificado este producto antes.'], 400);
        }

        // 3. Guardamos la nueva reseña
        $review = new Review();
        $review->user_id = Auth::id(); // Toma el ID del usuario logueado con el token
        $review->product_id = $request->product_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Opinión guardada exitosamente'
        ], 201);
    }
}
