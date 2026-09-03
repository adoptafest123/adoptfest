<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donaciones_especie', function (Blueprint $table) {
            $table->enum('especie_destino', ['perro', 'gato', 'otro', 'no_aplica'])
                  ->default('no_aplica')
                  ->after('categoria');

            // Dirección donde el repartidor debe recoger el insumo
            $table->string('direccion_recoleccion')->nullable()->after('descripcion');
            $table->string('telefono_contacto')->nullable()->after('direccion_recoleccion');
        });
    }

    public function down(): void
    {
        Schema::table('donaciones_especie', function (Blueprint $table) {
            $table->dropColumn(['especie_destino', 'direccion_recoleccion', 'telefono_contacto']);
        });
    }
};