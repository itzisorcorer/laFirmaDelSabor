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
        Schema::create('user_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products', 'product_id');
            
            //fecha en lo que lo vió
            $table->timestamp('viewed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_history');
    }
};
