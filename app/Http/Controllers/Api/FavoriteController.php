<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    //AGREGAR O QUITAR FAVORITO
    //POST /api/favorites/toogle
    public function toogle(Request $request){
        $request->validate([
            'product_id' => 'required|exists:products,product_id'
        ]);
        $user = $request->user();
        $productId = $request->product_id;

        //validar si ya habia sido guardado anteriormente
        $favorite = Favorite::where('user_id', $user->id)->
        where('product_id', $productId)->first();

        if($favorite){
            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado de favoritos',
                'is_favorite' => false
            ]);

        }else{
            //significa que no existia, lo creamos
            Favorite::create([
                'user_id' =>$user->id,
                'product_id' => $productId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado a favoritos',
                'is_favorite' => true
            ]);
        }
    }
}
