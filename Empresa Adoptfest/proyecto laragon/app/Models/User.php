<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'telefono',
        'foto',
        'descripcion'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
        const NIVELES_DONANTE = [
        'oro'    => 1000,
        'plata'  => 500,
        'bronce' => 200,
        'apoyo'  => 0,
    ];
 
    public function nivelDonante(): string
    {
        $puntos = $this->puntos_donante ?? 0;
 
        foreach (self::NIVELES_DONANTE as $nivel => $minimo) {
            if ($puntos >= $minimo) {
                return $nivel;
            }
        }
 
        return 'apoyo';
    }
 
    public function insigniaDonante(): array
    {
        return match ($this->nivelDonante()) {
            'oro'    => ['emoji' => '🥇', 'etiqueta' => 'Donante Oro',    'color' => '#d4af37'],
            'plata'  => ['emoji' => '🥈', 'etiqueta' => 'Donante Plata',  'color' => '#9ca3af'],
            'bronce' => ['emoji' => '🥉', 'etiqueta' => 'Donante Bronce', 'color' => '#b45309'],
            default  => ['emoji' => '🌱', 'etiqueta' => 'Apoyo',          'color' => '#16a34a'],
        };
    }
 
    public function sumarPuntosDonante(int $puntos): void
    {
        $this->increment('puntos_donante', $puntos);
    }
 
}
