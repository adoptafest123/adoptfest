<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donaciones_especie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('categoria', ['alimento', 'higiene', 'juguetes', 'cobijas_camas', 'medicamentos', 'otros']);
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->enum('estado', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');
            $table->unsignedInteger('puntos_otorgados')->default(0);
            $table->timestamp('confirmado_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donaciones_especie');
    }
};