<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    //GET api/categories-data
    public function index(){
        //primero, traemos todas las categorias
        $categories = DB::table('categories')->select('category_id', 'name')
        ->orderBy('name', 'asc')->get();

        //traemos las sub categorias
        $subcategories = DB::table('subcategories')
            ->select('subcategory_id', 'category_id', 'name')
            ->orderBy('name', 'asc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'subcategories' => $subcategories
                ]
            ]);

    }
}
