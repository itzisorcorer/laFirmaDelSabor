<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        //SEMBRADOR DE LA TABLA CATEGORIAS -- CATEGORIA ALIMENTOS
        $catAlimentos = Category::create([
            'name' => 'Alimentos',
            'image_url' => 'assets/categories/alimentos.jpg', //ruta ficticia solo para rellenar campo
        ]);
        //Subcateegorias de alimentos
        Subcategory::create(['category_id' => $catAlimentos->category_id, 'name' => 'Salsas']);
        Subcategory::create(['category_id' => $catAlimentos->category_id, 'name' => 'Fermentados']);
        Subcategory::create(['category_id' => $catAlimentos->category_id, 'name' => 'Postres']);
        Subcategory::create(['category_id' => $catAlimentos->category_id, 'name' => 'Platos fuertes']);


            //CATEGORIA BEBIDAS
        $catBebidas = Category::create([
        'name' => 'Bebidas',
        'image_url' => 'assets/categories/bebidas.jpg', //ruta ficticia solo para rellenar campo
    ]);

        //Subcateegorias de bebidas
        Subcategory::create(['category_id' => $catBebidas->category_id, 'name' => 'Alcoholicas']);
        Subcategory::create(['category_id' => $catBebidas->category_id, 'name' => 'No alcoholicas']);
        Subcategory::create(['category_id' => $catBebidas->category_id, 'name' => 'Jugos Naturales']);
        Subcategory::create(['category_id' => $catBebidas->category_id, 'name' => 'Aguas de sabor']);
        Subcategory::create(['category_id' => $catBebidas->category_id, 'name' => 'Bebidas Calientes']);


        //CATEGORÍA ESPECIALES
        $catEspeciales = Category::create([
            'name' => 'Especiales de Temporada',
            'image_url' => 'assets/categories/especiales.png' // ficticia para rellenar por ahora
        ]);

        //Subcategorias de especiales
        Subcategory::create(['category_id' => $catEspeciales->category_id, 'name' => 'Navidad']);
        Subcategory::create(['category_id' => $catEspeciales->category_id, 'name' => 'Día de muertos']);
    }
}
