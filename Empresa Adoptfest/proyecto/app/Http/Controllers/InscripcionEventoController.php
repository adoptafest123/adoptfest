<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Mascota;
use App\Models\MascotaEvento;
use App\Models\InscripcionEvento;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class InscripcionEventoController extends Controller
{
    // Muestra el formulario
    public function formulario($id)
    {
        $evento   = Evento::findOrFail($id);
        $mascotas = Mascota::where('estado', 'disponible')->get();

        return view('formulario_eventos', compact('evento', 'mascotas'));
    }

    // Guarda la inscripción
    public function guardar(Request $request, $id)
    {
        $request->validate([
            'telefono'   => 'required|string|max:20',
            'comentario' => 'nullable|string|max:500',
            'mascotas'   => 'nullable|array',
            'mascotas.*' => 'exists:mascotas,id',
        ]);

        // Evitar doble inscripción
        $yaInscrito = InscripcionEvento::where('user_id', session('id'))
            ->where('evento_id', $id)->exists();

        if ($yaInscrito) {
            return back()->with('error', '¡Ya estás inscrito en este evento!');
        }

        // Crear inscripción
        $inscripcion = InscripcionEvento::create([
            'user_id'    => session('id'),
            'evento_id'  => $id,
            'telefono'   => $request->telefono,
            'comentario' => $request->comentario,
            'estado'     => 'pendiente',
        ]);

        // Registrar mascotas seleccionadas
        if ($request->mascotas) {
            foreach ($request->mascotas as $mascotaId) {
                MascotaEvento::create([
                    'mascotas_id'    => $mascotaId,
                    'eventos_id'     => $id,
                    'inscripcion_id' => $inscripcion->id,
                ]);
            }
        }

        // Notificar al usuario que su solicitud fue recibida
        Notificacion::create([
            'user_id' => session('id'),
            'titulo'  => '📋 Solicitud recibida',
            'mensaje' => 'Tu inscripción al evento "' . ($inscripcion->evento->titulo ?? 'evento') . '" fue enviada y está pendiente de revisión.',
            'tipo'    => 'info',
        ]);

        return redirect('/eventos')->with('exito', '¡Inscripción enviada! El admin revisará tu solicitud.');
    }

    // ─── ADMIN 

    // Lista todas las inscripciones
    public function adminIndex()
    {
        $inscripciones = InscripcionEvento::with(['user', 'evento'])
            ->orderByRaw("FIELD(estado,'pendiente','aceptado','rechazado')")
            ->latest()
            ->get();

        // Orden interno por nivel de donante (mayor a menor) dentro de cada
        // grupo de estado, para que el admin vea primero a los solicitantes
        // con más puntos. Solo afecta el orden de la lista — el dato de
        // puntos en sí no se imprime en la vista, solo la insignia.
        $ordenNivel = ['oro' => 3, 'plata' => 2, 'bronce' => 1, 'apoyo' => 0];

        $inscripciones = $inscripciones->sortBy(function ($i) use ($ordenNivel) {
            $nivel = $i->user ? $i->user->nivelDonante() : 'apoyo';
            // Negativo para que el de mayor nivel quede primero al ordenar asc
            return -$ordenNivel[$nivel];
        })->values();

        // Re-agrupar respetando primero el estado (pendiente, aceptado, rechazado)
        // y dentro de cada estado, el nivel de donante ya aplicado arriba.
        $inscripciones = $inscripciones->sortBy(function ($i) {
            return match ($i->estado) {
                'pendiente' => 0,
                'aceptado'  => 1,
                'rechazado' => 2,
                default     => 3,
            };
        })->values();

        return view('admin_inscripciones', compact('inscripciones'));
    }

    // Acepta o rechaza con observaciones
    public function adminActualizar(Request $request, $id)
    {
        $request->validate([
            'estado'        => 'required|in:aceptado,rechazado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $inscripcion = InscripcionEvento::with('evento')->findOrFail($id);

        $inscripcion->update([
            'estado'        => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        // Actualizar estado de mascotas
        $estadoMascota = $request->estado === 'aceptado' ? 'en_evento' : 'disponible';
        $mascotas = MascotaEvento::where('inscripcion_id', $id)->pluck('mascotas_id');
        Mascota::whereIn('id', $mascotas)->update(['estado' => $estadoMascota]);

        // ── Crear notificación para el usuario 
        $tituloEvento = $inscripcion->evento->titulo ?? 'el evento';

        if ($request->estado === 'aceptado') {
            $titulo  = '✅ Inscripción aceptada';
            $mensaje = '¡Felicidades! Tu inscripción al evento "' . $tituloEvento . '" fue aceptada.';
            if ($request->observaciones) {
                $mensaje .= ' Mensaje del administrador: ' . $request->observaciones;
            }
            $tipo = 'aceptado';
        } else {
            $titulo  = '❌ Inscripción rechazada';
            $mensaje = 'Lamentablemente, tu inscripción al evento "' . $tituloEvento . '" fue rechazada.';
            if ($request->observaciones) {
                $mensaje .= ' Motivo: ' . $request->observaciones;
            }
            $tipo = 'rechazado';
        }

        Notificacion::create([
            'user_id' => $inscripcion->user_id,
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'tipo'    => $tipo,
        ]);
       
        return back()->with('exito', 'Inscripción actualizada correctamente.');
    }
    // Elimina una inscripción (solo admin)
public function adminEliminar($id)
{
    $inscripcion = InscripcionEvento::with('evento')->findOrFail($id);

    // Liberar las mascotas asociadas antes de eliminar
    $mascotas = MascotaEvento::where('inscripcion_id', $id)->pluck('mascotas_id');
    Mascota::whereIn('id', $mascotas)->update(['estado' => 'disponible']);

    // Eliminar los registros de mascota-evento
    MascotaEvento::where('inscripcion_id', $id)->delete();

    // Notificar al usuario
    Notificacion::create([
        'user_id' => $inscripcion->user_id,
        'titulo'  => '🗑️ Inscripción eliminada',
        'mensaje' => 'Tu inscripción al evento "' . ($inscripcion->evento->titulo ?? 'el evento') . '" fue eliminada por el administrador.',
        'tipo'    => 'info',
    ]);

    $inscripcion->delete();

    return back()->with('exito', 'Inscripción eliminada correctamente.');
}
}