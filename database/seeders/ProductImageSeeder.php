<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$now = Carbon::now();

        
        DB::table('product_images')->truncate();

        
        $images = [
            // Producto 2: Salsa de Tomate artesanal
            ['product_id' => 2, 'image_url' => 'https://lacocinadesombravieja.com/wp-content/uploads/2018/07/salsa-de-tomate.jpg', 'is_primary' => true],
            ['product_id' => 2, 'image_url' => 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?auto=format&fit=crop&q=80&w=800', 'is_primary' => false], // Foto secundaria

            // Producto 16: Salsa Verde Martajada
            ['product_id' => 16, 'image_url' => 'https://www.mexicoinmykitchen.com/wp-content/uploads/2009/01/salsa-verde-recipe-1-1.jpg', 'is_primary' => true],
            ['product_id' => 16, 'image_url' => 'https://www.mexicoinmykitchen.com/wp-content/uploads/2009/01/salsa-verde-recipe-1-1.jpg', 'is_primary' => false],

            // Producto 17: Mole Poblano Artesanal
            ['product_id' => 17, 'image_url' => 'https://www.mexicodesconocido.com.mx/wp-content/uploads/2010/09/Mole-poblano-1024x640.jpg', 'is_primary' => true],
            ['product_id' => 17, 'image_url' => 'https://www.mexicodesconocido.com.mx/wp-content/uploads/2010/09/Mole-poblano-1024x640.jpg', 'is_primary' => false],

            // Producto 18: Miel de Agave Orgánica
            ['product_id' => 18, 'image_url' => 'http://mieldeagave.com.mx/wp-content/uploads/2015/07/miel.jpg', 'is_primary' => true],
            ['product_id' => 18, 'image_url' => 'http://mieldeagave.com.mx/wp-content/uploads/2015/07/miel.jpg', 'is_primary' => false],

            // Producto 19: Café de Olla Tostado
            ['product_id' => 19, 'image_url' => 'https://images.unsplash.com/photo-1559525839-b184a4d698c7?auto=format&fit=crop&q=80&w=800', 'is_primary' => true],
            ['product_id' => 19, 'image_url' => 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&q=80&w=800', 'is_primary' => false], // Foto secundaria

            // Producto 20: Mermelada de Guayaba
            ['product_id' => 20, 'image_url' => 'https://www.sabor.eluniverso.com/wp-content/uploads/2024/04/shutterstock_592356647-1536x1024.jpg', 'is_primary' => true],
            ['product_id' => 20, 'image_url' => 'https://www.sabor.eluniverso.com/wp-content/uploads/2024/04/shutterstock_592356647-1536x1024.jpg', 'is_primary' => false],

            // Producto 21: Tepache Añejo
            ['product_id' => 21, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=800', 'is_primary' => true],
            ['product_id' => 21, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=800', 'is_primary' => false],
        ];

        // 🚀 Insertamos todo de golpe
        $dataToInsert = [];
        foreach($images as $img) {
            $dataToInsert[] = [
                'product_id' => $img['product_id'],
                'image_url' => $img['image_url'],
                'is_primary' => $img['is_primary'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_images')->insert($dataToInsert);
    
    }
}
