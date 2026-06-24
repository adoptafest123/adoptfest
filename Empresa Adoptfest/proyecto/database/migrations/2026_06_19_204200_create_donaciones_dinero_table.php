<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donaciones_dinero', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 10)->default('USD');
            $table->string('paypal_order_id')->unique();
            $table->enum('estado', ['pendiente', 'completado', 'fallido'])->default('pendiente');
            $table->unsignedInteger('puntos_otorgados')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donaciones_dinero');
    }
};