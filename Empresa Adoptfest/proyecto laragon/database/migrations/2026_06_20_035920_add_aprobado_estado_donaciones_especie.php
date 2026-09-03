<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL no permite modificar un ENUM directo con Schema::table de forma
        // sencilla, así que usamos SQL crudo para ampliar los valores permitidos.
        DB::statement("ALTER TABLE donaciones_especie MODIFY estado ENUM('pendiente','aprobado','confirmado','rechazado') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE donaciones_especie MODIFY estado ENUM('pendiente','confirmado','rechazado') DEFAULT 'pendiente'");
    }
};