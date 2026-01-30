<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

//APARTADO RUTAS PÚBLICAS
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//producto
Route::get('/products', [ProductController::class, 'index']); //ver productos catálogo

//APARTADO RUTAS PRIVADAS
Route::middleware('auth:sanctum')->group(function (){
    //ruta para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    //ruta para obtener mis datos
    Route::get('/user', function(Request $request){
        return $request->user();
    });
    //crear producto (protegido)
    Route::post('/products', [ProductController::class, 'store']);

});

//RUTAS DE PRODUCTOS



//RUTAS DE PEDIDOS
