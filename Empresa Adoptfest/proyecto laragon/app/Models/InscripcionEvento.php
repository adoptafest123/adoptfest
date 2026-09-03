<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InscripcionEvento extends Model
{
    protected $table = 'inscripciones_eventos';
    protected $fillable = ['user_id', 'evento_id', 'telefono', 'comentario', 'estado'];

    public function user()    { return $this->belongsTo(User::class, 'user_id'); }
    public function evento()  { return $this->belongsTo(Evento::class, 'evento_id'); }
    public function mascotas() {
        return $this->hasManyThrough(
            Mascota::class,
            MascotaEvento::class,
            'inscripcion_id',   // FK en mascotas_eventos
            'id',
            'id',
            'mascota_id'
        );
    }
}