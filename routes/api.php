<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductVideoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CreatorController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GestorController;
use App\Http\Controllers\Api\AdminController;

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
    //productos (protegido)
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    //RUTA DE FAVORITOS 
    Route::post('/favorites/toggle', [FavoriteController::class, 'toogle']);

    //ruta para videos
    Route::post('/products/{id}/videos', [ProductVideoController::class, 'store']);

    //pedidos
    Route::post('/orders', [OrderController::class, 'store']); //comprar
    Route::get('/orders', [OrderController::class, 'getMyOrders']); //ver historial



    //ruta para obtener la info de Home:
    Route::get('/home', [HomeController::class, 'index']);


    //Rutas de Creadoras
    Route::get('/creators', [CreatorController::class, 'index']);
    Route::post('/creators', [CreatorController::class, 'store']);


    //BUSCADOR DE HOME
    Route::get('/search', [ProductController::class, 'search']);

    //RUTA PARA EL FILTRO
    Route::get('/categories/{id}/products', [ProductController::class, 'getByCategory']);

    //RUTA DE CHECKOUT
    Route::post('/checkout', [OrderController::class, 'checkout']);

    //visualizar perfil de creadora
    Route::get('/creators/{id}', [CreatorController::class, 'getProfile']);

    //Rutas de gestor:
    Route::get('/gestor/orders', [GestorController::class, 'getAllOrders']);
    Route::get('/gestor/admins', [GestorController::class, 'getAvailableAdmins']);
    Route::put('/gestor/orders/{id}', [GestorController::class, 'updateOrder']);

    //RUTAS DE ADMIN
    Route::get('/admin/orders', [AdminController::class, 'getMyAssignedOrders']);
    Route::put('/admin/orders/{id}', [AdminController::class, 'updateOrderStatus']);

});

//RUTAS DE PRODUCTOS



//RUTAS DE PEDIDOS
