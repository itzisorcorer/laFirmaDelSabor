<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            //llaves foraneas
            $table->foreignId('subcategory_id')->constrained();
            $table->foreignId('user_id')->constrained();

            //datos del producto
            $table->string('name', 50);
            $table->string('description', 100);
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('status')->default(true);

            //descripcion de la accesibilidad
            $table->text('accessibility_description')->nullable();

            //POR CHECAR
            $table->string('main_image_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
