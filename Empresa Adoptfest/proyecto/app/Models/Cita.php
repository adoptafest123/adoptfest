<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'mascota_id',
        'fecha',
        'hora',
        'lugar',
        'direccion_cita',
        'notas',
        'codigo_verificacion',
        'estado',
        'verificada',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudAdopcion::class, 'solicitud_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }
}