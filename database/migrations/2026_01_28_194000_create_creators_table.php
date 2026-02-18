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
        Schema::create('creators', function (Blueprint $table) {
            $table->id('creator_id');

            $table->string('name');
            $table->text('biography')->nullable();
            $table->string('location')->nullable();

            //perfil y portada
            $table->string('photo_url')->nullable();
            $table->string('cover_photo_url')->nullable();
            $table->decimal('rating_avg', 3,2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
