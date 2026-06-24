<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonacionEspecie extends Model
{
    protected $table = 'donaciones_especie';

    protected $fillable = [
        'user_id',
        'categoria',
        'especie_destino',
        'descripcion',
        'cantidad',
        'direccion_recoleccion',
        'telefono_contacto',
        'estado',
        'puntos_otorgados',
        'confirmado_at',
    ];

    protected $casts = [
        'confirmado_at' => 'datetime',
    ];

    const PUNTOS_POR_CATEGORIA = [
        'alimento'      => 50,
        'higiene'       => 30,
        'juguetes'      => 15,
        'cobijas_camas' => 40,
        'medicamentos'  => 60,
        'otros'         => 10,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cita()
    {
        return $this->hasOne(CitaDonacion::class, 'donacion_especie_id');
    }

    public function calcularPuntos(): int
    {
        $valorUnidad = self::PUNTOS_POR_CATEGORIA[$this->categoria] ?? 0;
        return $valorUnidad * max(1, $this->cantidad);
    }
}