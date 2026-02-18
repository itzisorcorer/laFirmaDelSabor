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
        Schema::create('product_videos', function (Blueprint $table) {
            $table->id('product_video_id');

            //muchos videos pertenecen a un producto
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->string('url_youtube');
            $table->text('accessibility_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
