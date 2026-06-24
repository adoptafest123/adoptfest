<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donaciones_dinero', function (Blueprint $table) {
            $table->boolean('oculto_para_usuario')->default(false)->after('estado');
        });

        Schema::table('donaciones_especie', function (Blueprint $table) {
            $table->boolean('oculto_para_usuario')->default(false)->after('estado');
        });

        Schema::table('inscripciones_eventos', function (Blueprint $table) {
            $table->boolean('oculto_para_usuario')->default(false)->after('estado');
        });

        Schema::table('solicitudes_adopcion', function (Blueprint $table) {
            $table->boolean('oculto_para_usuario')->default(false)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('donaciones_dinero', function (Blueprint $table) {
            $table->dropColumn('oculto_para_usuario');
        });
        Schema::table('donaciones_especie', function (Blueprint $table) {
            $table->dropColumn('oculto_para_usuario');
        });
        Schema::table('inscripciones_eventos', function (Blueprint $table) {
            $table->dropColumn('oculto_para_usuario');
        });
        Schema::table('solicitudes_adopcion', function (Blueprint $table) {
            $table->dropColumn('oculto_para_usuario');
        });
    }
};