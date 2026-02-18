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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');

            $table->foreignId('user_id')->constrained('users'); //para el comprador

            $table->foreignId('assigned_admin_id')->nullable()->constrained('users'); //para el admin asignado a la orden

            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending', //pendiente
                'in_progress', //en progreso
                'labeled', //etiquetado
                'unassigned', //por asignar
                'in_transit', // en camino
                'completed', // finalizado
                'canceled', // cancelado
                'delivered' // entregado
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
