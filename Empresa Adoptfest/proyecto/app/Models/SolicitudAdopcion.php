<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SolicitudAdopcion extends Model
{
    protected $table = 'solicitudes_adopcion';
    protected $fillable = [
        'user_id', 'mascota_id', 'nombre_completo', 'cedula',
        'telefono', 'direccion', 'ciudad', 'tipo_vivienda',
        'tiene_patio', 'es_propia', 'tiene_ninos', 'edades_ninos',
        'tiene_otros_animales', 'cuales_animales', 'personas_en_casa',
        'tiene_experiencia', 'descripcion_experiencia', 'horas_sola_mascota',
        'quien_cuida_ausencia', 'motivo_adopcion', 'compromiso',
        'estado', 'observaciones',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function mascota() { return $this->belongsTo(Mascota::class); }
    public function cita()    { return $this->hasOne(\App\Models\Cita::class, 'solicitud_id');}
}
