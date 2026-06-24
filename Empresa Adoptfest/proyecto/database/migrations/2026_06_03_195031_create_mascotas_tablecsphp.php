<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('edad')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('historia')->nullable();
            $table->string('imagen')->nullable();
            $table->enum('estado', [
                'disponible',
                'en_evento',
                'proceso',
                'adoptado'
            ])->default('disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};