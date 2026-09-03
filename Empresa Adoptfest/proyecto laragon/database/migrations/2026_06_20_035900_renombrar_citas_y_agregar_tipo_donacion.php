<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Renombrar la tabla
        Schema::rename('citas_adopcion', 'citas');

        Schema::table('citas', function (Blueprint $table) {
            // Tipo de cita: adopcion (flujo actual) o donacion (flujo nuevo)
            $table->enum('tipo', ['adopcion', 'donacion'])->default('adopcion')->after('id');

            // Ahora opcionales — antes eran obligatorios, solo aplicaban a adopción
            $table->unsignedBigInteger('solicitud_id')->nullable()->change();
            $table->unsignedBigInteger('mascota_id')->nullable()->change();

            // Nuevo: referencia a la donación en especie, solo cuando tipo = 'donacion'
            $table->unsignedBigInteger('donacion_especie_id')->nullable()->after('mascota_id');
            $table->foreign('donacion_especie_id')
                  ->references('id')->on('donaciones_especie')
                  ->onDelete('cascade');

            // Dirección de recolección (solo aplica a citas de tipo donación)
            $table->string('direccion_recoleccion')->nullable()->after('direccion_cita');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['donacion_especie_id']);
            $table->dropColumn(['tipo', 'donacion_especie_id', 'direccion_recoleccion']);
        });

        Schema::rename('citas', 'citas_adopciones');
    }
};