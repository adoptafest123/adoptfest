<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaDonacion extends Model
{
    protected $table = 'citas_donaciones';

    protected $fillable = [
        'user_id',
        'donacion_especie_id',
        'fecha',
        'hora',
        'direccion_recoleccion',
        'notas',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function donacionEspecie()
    {
        return $this->belongsTo(DonacionEspecie::class, 'donacion_especie_id');
    }
}