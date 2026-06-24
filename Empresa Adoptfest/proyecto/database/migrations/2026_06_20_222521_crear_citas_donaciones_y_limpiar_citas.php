<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla nueva, exclusiva para recolección de donaciones
        Schema::create('citas_donaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('donacion_especie_id');
            $table->date('fecha');
            $table->time('hora');
            $table->string('direccion_recoleccion');
            $table->text('notas')->nullable();
            $table->enum('estado', ['programada', 'completada', 'cancelada'])->default('programada');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('donacion_especie_id')->references('id')->on('donaciones_especie')->onDelete('cascade');
        });

        // 2. Quitar de "citas" lo que solo se usó temporalmente para donaciones
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['donacion_especie_id']);
            $table->dropColumn(['tipo', 'donacion_especie_id', 'direccion_recoleccion']);
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->enum('tipo', ['adopcion', 'donacion'])->default('adopcion')->after('id');
            $table->unsignedBigInteger('donacion_especie_id')->nullable()->after('mascota_id');
            $table->string('direccion_recoleccion')->nullable()->after('direccion_cita');
            $table->foreign('donacion_especie_id')->references('id')->on('donaciones_especie')->onDelete('cascade');
        });

        Schema::dropIfExists('citas_donaciones');
    }
};