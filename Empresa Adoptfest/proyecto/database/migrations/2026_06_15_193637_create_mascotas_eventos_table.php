<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascotas_id');
            $table->unsignedBigInteger('eventos_id');
            $table->unsignedBigInteger('inscripcion_id')->nullable();
            $table->timestamps();

            $table->foreign('mascotas_id')->references('id')->on('mascotas')->onDelete('cascade');
            $table->foreign('eventos_id')->references('id')->on('eventos')->onDelete('cascade');
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones_eventos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas_eventos');
    }
};