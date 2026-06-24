<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    // Relación: pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope: solo no leídas
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }
}