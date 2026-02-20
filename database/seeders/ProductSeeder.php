<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Limpiamos la tabla para evitar productos duplicados si corres el comando varias veces
 

        $now = Carbon::now();

        $products = [
            [
                'name' => 'Salsa Verde Martajada',
                'description' => 'Auténtica salsa verde de molcajete con tomatillo, chile serrano y un toque de cilantro fresco.',
                'price' => 45.00,
                'main_image_url' => 'https://spicedblog.com/wp-content/uploads/2022/06/Salsa-Verde1.jpg', 
                'creator_id' => 1, // Doña Carmelita
                'subcategory_id' => 1, // Salsas
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Mole Poblano Artesanal',
                'description' => 'Pasta de mole tradicional elaborada con más de 20 ingredientes, notas de cacao y chiles secos.',
                'price' => 120.00,
                'main_image_url' => 'https://www.pequerecetas.com/wp-content/uploads/2025/03/mole-poblano-receta-tradicional.jpg',
                'creator_id' => 1,
                'subcategory_id' => 4, // Platos fuertes
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Miel de Agave Orgánica',
                'description' => 'Endulzante natural extraído directamente del agave azul, perfecto para bebidas y postres.',
                'price' => 85.00,
                'main_image_url' => 'https://cdn.brujulabike.com/media/14101/conversions/img-sirope-de-agave-hd-1280x720-img-sirope-de-agave-hd-1280x720-1000.jpg',
                'creator_id' => 1,
                'subcategory_id' => 3, // Postres
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Café de Olla Tostado',
                'description' => 'Mezcla de granos arábica con un toque de canela y piloncillo, listo para preparar.',
                'price' => 150.00,
                'main_image_url' => 'https://images.unsplash.com/photo-1559525839-b184a4d698c7?q=80&w=500&auto=format&fit=crop',
                'creator_id' => 1,
                'subcategory_id' => 9, // Bebidas calientes
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Mermelada de Guayaba',
                'description' => 'Conserva casera elaborada con guayabas de temporada, baja en azúcar.',
                'price' => 60.00,
                'main_image_url' => 'https://recetacubana.com/wp-content/uploads/2018/11/mermelada-de-guayaba.avif',
                'creator_id' => 1,
                'subcategory_id' => 3, // Postres
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tepache Añejo',
                'description' => 'Bebida fermentada de piña con un toque de clavo y especias, muy refrescante.',
                'price' => 35.00,
                'main_image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?q=80&w=500&auto=format&fit=crop',
                'creator_id' => 1,
                'subcategory_id' => 2, // Fermentados
                'user_id' => 1, // Admin
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('products')->insert($products);
    }
}