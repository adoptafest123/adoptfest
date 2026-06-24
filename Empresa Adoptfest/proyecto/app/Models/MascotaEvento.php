<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MascotaEvento extends Model
{
    protected $table = 'mascotas_eventos';
    protected $fillable = ['mascotas_id', 'eventos_id', 'inscripcion_id'];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascotas_id');
    }
    
}