<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //REGISTRO DE COMPRADORES
    //Endpoint a usar: POST /api/register

    public function register(Request $request){

    //validar lo que envía el usuario
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:60|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone_number' => 'nullable|string|max:15',
        ]);

        //aqui traemos al usuario de la BD 
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'role' => 'buyer', //registrar siempre como comprador
        ]);

        //aqui creamos el token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        //enviar el token a flutter
        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }


    //INICIO DE SESIÓN
    //Endpoint a usar: POST /api/login
    public function login(Request $request){
        //primero se validan las credenciales recibidas
        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);

        }
        //Si existe el usuario:
        $user = User::where('email', $request->email)->firstOrFail();

        //crear token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        //responder a flutter
        return response()->json([
            'message' => 'Hola de nuevo, ' . $user->name,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    //CERRAR SESIÓN
    //Endpoint a usar: POST /api/logout
    //requiere header: bearer token
    public function logout(Request $request){
    //borramos el token de esta sesión    
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Sesión cerrada correctamente'
    ]);
    }
}
