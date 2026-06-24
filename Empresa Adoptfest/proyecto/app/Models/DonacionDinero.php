<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonacionDinero extends Model
{
    protected $table = 'donaciones_dinero';

    protected $fillable = [
        'user_id',
        'monto',
        'moneda',
        'paypal_order_id',
        'estado',
        'puntos_otorgados',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}