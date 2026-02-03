<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Http\Request;

class ProductVideoController extends Controller
{
    /**
     * AGREGAR UN VIDEO AL PRODUCTO
     * POST api/products/{id}/videos
    */
    public function store(Request $request, $productId){
        //verificar que el producto realmente exista
        $product = Product::findOrFail($productId);

        $validated = $request->validated([
            'title' => 'required|string|max150',
            'url_youtube' => 'required|url', //este valida que sea https...
            'accessibility_description' => 'required|string',
        ]);

        //si todo va bien, guardamos en video
        $video = ProductVideo::create([
            'product_id' => $product->id,
            'title' => $validated['title'],
            'url_youtube' => $validated['url_youtube'],
            'accessibility_description' => $validated['accessibility_description'],
        ]);
        return response()->json([
            'message' => 'Video agregado correctamente al producto',
            'video' => $video
        ], 201);
    }
}
