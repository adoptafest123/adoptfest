<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'lugar',
        'fecha',
        'imagen',
        'estado',
        'categoria',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
}