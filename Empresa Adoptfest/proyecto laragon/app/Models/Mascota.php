<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    protected $table = 'mascotas';

    protected $fillable = [
        'tipo', 
        'codigo',
        'nombre',
        'edad',
        'descripcion',
        'historia',
        'imagen',
        'estado',
    ];

    // Estados posibles
    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_EN_EVENTO  = 'en_evento';
    const ESTADO_PROCESO    = 'proceso';
    const ESTADO_ADOPTADO   = 'adoptado';
}