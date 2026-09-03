<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mascota;
use App\Models\SolicitudAdopcion;
use App\Models\Cita;
use App\Models\CitaDonacion;
use App\Models\DonacionEspecie;
use App\Models\Notificacion;

class AdopcionController extends Controller
{
    private function generarCodigo(): string
    {
        do {
            $codigo = 'ADPT-' . date('Y') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ0123456789'), 0, 6));
        } while (Cita::where('codigo_verificacion', $codigo)->exists());
        return $codigo;
    }

    public function formulario($id)
    {
        $mascota = Mascota::findOrFail($id);
        return view('formulario_adopcion', compact('mascota'));
    }

    public function guardar(Request $request, $id)
    {
        $request->validate([
            'nombre_completo'         => 'required|min:5|max:150',
            'cedula'                  => 'required|digits_between:6,10',
            'telefono'                => 'required|digits:10',
            'direccion'               => 'required|max:255',
            'ciudad'                  => 'required|max:100',
            'tipo_vivienda'           => 'required|in:casa,apartamento,finca',
            'personas_en_casa'        => 'required|integer|min:1|max:20',
            'horas_sola_mascota'      => 'required|integer|min:0|max:24',
            'motivo_adopcion'         => 'required|min:20',
            'compromiso'              => 'nullable|max:1000',
            'descripcion_experiencia' => 'nullable|max:1000',
            'quien_cuida_ausencia'    => 'nullable|max:255',
            'edades_ninos'            => 'nullable|max:100',
            'cuales_animales'         => 'nullable|max:255',
        ], [
            'cedula.digits_between' => 'La cédula debe tener entre 6 y 10 dígitos, solo números.',
            'cedula.required'       => 'La cédula es obligatoria.',
            'telefono.digits'       => 'El teléfono debe tener exactamente 10 dígitos.',
            'telefono.required'     => 'El teléfono es obligatorio.',
            'personas_en_casa.max'  => 'Máximo 20 personas.',
        ]);

        $yaExiste = SolicitudAdopcion::where('user_id', session('id'))
            ->where('mascota_id', $id)
            ->whereIn('estado', ['pendiente', 'aceptada'])
            ->exists();

        if ($yaExiste) {
            return back()->with('error', 'Ya tienes una solicitud activa para esta mascota.');
        }

        $mascota = Mascota::findOrFail($id);

        SolicitudAdopcion::create([
            'user_id'                 => session('id'),
            'mascota_id'              => $id,
            'nombre_completo'         => $request->nombre_completo,
            'cedula'                  => $request->cedula,
            'telefono'                => $request->telefono,
            'direccion'               => $request->direccion,
            'ciudad'                  => $request->ciudad,
            'tipo_vivienda'           => $request->tipo_vivienda,
            'tiene_patio'             => $request->has('tiene_patio') ? 1 : 0,
            'es_propia'               => $request->has('es_propia') ? 1 : 0,
            'tiene_ninos'             => $request->has('tiene_ninos') ? 1 : 0,
            'edades_ninos'            => $request->edades_ninos,
            'tiene_otros_animales'    => $request->has('tiene_otros_animales') ? 1 : 0,
            'cuales_animales'         => $request->cuales_animales,
            'personas_en_casa'        => $request->personas_en_casa,
            'tiene_experiencia'       => $request->has('tiene_experiencia') ? 1 : 0,
            'descripcion_experiencia' => $request->descripcion_experiencia,
            'horas_sola_mascota'      => $request->horas_sola_mascota,
            'quien_cuida_ausencia'    => $request->quien_cuida_ausencia,
            'motivo_adopcion'         => $request->motivo_adopcion,
            'compromiso'              => $request->compromiso,
            'estado'                  => 'pendiente',
        ]);

        Notificacion::create([
            'user_id' => session('id'),
            'titulo'  => '📋 Solicitud de adopción enviada',
            'mensaje' => 'Tu solicitud para adoptar a "' . $mascota->nombre . '" fue recibida. El equipo la revisará pronto.',
            'tipo'    => 'info',
        ]);

        return redirect('/adopcion')->with('exito', '¡Solicitud enviada! Te notificaremos pronto.');
    }

    public function adminIndex()
    {
        $solicitudes = SolicitudAdopcion::with(['user', 'mascota', 'cita'])
            ->orderByRaw("FIELD(estado,'pendiente','aceptada','rechazada')")
            ->latest()
            ->get();

        return view('admin_adopciones', compact('solicitudes'));
    }

    public function responder(Request $request, $id)
    {
        $request->validate([
            'estado'        => 'required|in:aceptada,rechazada',
            'observaciones' => 'nullable|max:500',
        ]);

        $solicitud = SolicitudAdopcion::with(['mascota', 'user'])->findOrFail($id);
        $solicitud->update([
            'estado'        => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        if ($request->estado === 'aceptada') {
            if ($solicitud->mascota) {
                $solicitud->mascota->update(['estado' => 'proceso']);
            }
            Notificacion::create([
                'user_id' => $solicitud->user_id,
                'titulo'  => '✅ ¡Solicitud aceptada!',
                'mensaje' => 'Tu solicitud para adoptar a "' . ($solicitud->mascota->nombre ?? 'la mascota') . '" fue aceptada. Pronto recibirás los detalles de tu cita.',
                'tipo'    => 'aceptado',
            ]);
        } else {
            Notificacion::create([
                'user_id' => $solicitud->user_id,
                'titulo'  => '❌ Solicitud rechazada',
                'mensaje' => 'Tu solicitud para adoptar a "' . ($solicitud->mascota->nombre ?? 'la mascota') . '" fue rechazada.' .
                             ($request->observaciones ? ' Motivo: ' . $request->observaciones : ''),
                'tipo'    => 'rechazado',
            ]);
        }

        return back()->with('exito', 'Solicitud actualizada correctamente.');
    }

    public function adminCitas()
    {
        $citasAdopcion = Cita::with(['user', 'mascota', 'solicitud'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        $solicitudesAceptadas = SolicitudAdopcion::with(['user', 'mascota'])
            ->where('estado', 'aceptada')
            ->whereDoesntHave('cita')
            ->get();

        $citasDonacion = CitaDonacion::with(['user', 'donacionEspecie'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        $donacionesAprobadas = DonacionEspecie::with('user')
            ->where('estado', 'aprobado')
            ->whereDoesntHave('cita')
            ->get();

        return view('admin_citas', compact(
            'citasAdopcion',
            'solicitudesAceptadas',
            'citasDonacion',
            'donacionesAprobadas'
        ));
    }

    public function agendar(Request $request, $id)
    {
        $request->validate([
            'fecha'          => 'required|date|after:today',
            'hora'           => 'required',
            'lugar'          => 'required|max:255',
            'direccion_cita' => 'nullable|max:255',
            'notas'          => 'nullable|max:500',
        ]);

        $solicitud = SolicitudAdopcion::with(['mascota', 'user'])->findOrFail($id);

        if (!$solicitud->mascota) {
            return back()->with('error', 'No se encontró la mascota asociada a esta solicitud.');
        }

        $codigo = $this->generarCodigo();

        Cita::create([
            'solicitud_id'        => $solicitud->id,
            'user_id'             => $solicitud->user_id,
            'mascota_id'          => $solicitud->mascota_id,
            'fecha'               => $request->fecha,
            'hora'                => $request->hora,
            'lugar'               => $request->lugar,
            'direccion_cita'      => $request->direccion_cita,
            'notas'               => $request->notas,
            'codigo_verificacion' => $codigo,
            'estado'              => 'programada',
        ]);

        Notificacion::create([
            'user_id' => $solicitud->user_id,
            'titulo'  => '📅 ¡Tu cita está programada!',
            'mensaje' => '🐾 Mascota: ' . $solicitud->mascota->nombre .
                         ' | 📅 Fecha: ' . $request->fecha .
                         ' | ⏰ Hora: ' . $request->hora .
                         ' | 📍 Lugar: ' . $request->lugar .
                         ($request->direccion_cita ? ' — ' . $request->direccion_cita : '') .
                         ' | 🔐 Tu código de verificación: ' . $codigo .
                         ' (Preséntalo el día de la cita)',
            'tipo'    => 'cita',
        ]);

        return back()->with('exito', 'Cita agendada. Código ' . $codigo . ' enviado al usuario.');
    }

    /**
     * Cambia el estado de una cita de ADOPCIÓN (tabla "citas").
     */
    public function cambiarEstadoCita(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:programada,completada,cancelada',
        ]);

        $cita = Cita::with('mascota')->findOrFail($id);
        $cita->update(['estado' => $request->estado]);

        if ($cita->mascota) {
            if ($request->estado === 'completada') {
                $cita->mascota->update(['estado' => 'adoptado']);
            } elseif ($request->estado === 'cancelada') {
                $cita->mascota->update(['estado' => 'disponible']);
            }
        }

        return back()->with('exito', 'Estado de cita actualizado.');
    }

    /**
     * Agenda una cita de recolección de DONACIÓN (tabla "citas_donaciones").
     */
    public function agendarRecoleccion(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date|after:today',
            'hora'  => 'required',
            'notas' => 'nullable|max:500',
        ], [
            'fecha.required' => 'Selecciona la fecha de recolección.',
            'fecha.after'    => 'La fecha debe ser posterior a hoy.',
            'hora.required'  => 'Selecciona la hora de recolección.',
        ]);

        $donacion = DonacionEspecie::with('user')->findOrFail($id);

        if ($donacion->estado !== 'aprobado') {
            return back()->with('error', 'Esta donación debe estar aprobada antes de agendar la recolección.');
        }

        CitaDonacion::create([
            'user_id'                => $donacion->user_id,
            'donacion_especie_id'    => $donacion->id,
            'fecha'                  => $request->fecha,
            'hora'                   => $request->hora,
            'direccion_recoleccion'  => $donacion->direccion_recoleccion,
            'notas'                  => $request->notas,
            'estado'                 => 'programada',
        ]);

        Notificacion::create([
            'user_id' => $donacion->user_id,
            'titulo'  => '🚚 Recolección programada',
            'mensaje' => 'Pasaremos a recoger tu donación el ' . $request->fecha .
                         ' a las ' . $request->hora . ' en la dirección que indicaste.' .
                         ($request->notas ? ' Notas: ' . $request->notas : ''),
            'tipo'    => 'cita',
        ]);

        return back()->with('exito', 'Recolección agendada. El donante fue notificado.');
    }

    /**
     * Cambia el estado de una cita de DONACIÓN (tabla "citas_donaciones").
     * Al completarse, aquí se otorgan los puntos al donante.
     */
    public function cambiarEstadoCitaDonacion(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:programada,completada,cancelada',
        ]);

        $cita = CitaDonacion::with('donacionEspecie.user')->findOrFail($id);
        $cita->update(['estado' => $request->estado]);

        if ($cita->donacionEspecie) {
            if ($request->estado === 'completada') {
                $donacion = $cita->donacionEspecie;
                $puntos   = $donacion->calcularPuntos();

                $donacion->update([
                    'estado'           => 'confirmado',
                    'puntos_otorgados' => $puntos,
                    'confirmado_at'    => now(),
                ]);

                if ($donacion->user) {
                    $donacion->user->sumarPuntosDonante($puntos);
                }

                Notificacion::create([
                    'user_id' => $donacion->user_id,
                    'titulo'  => '✅ Donación recibida, ¡gracias!',
                    'mensaje' => 'Confirmamos la recolección de tu donación. ¡Gracias por apoyar a Adoptafest! 🐾',
                    'tipo'    => 'aceptado',
                ]);
            } elseif ($request->estado === 'cancelada') {
                $cita->donacionEspecie->update(['estado' => 'pendiente']);
            }
        }

        return back()->with('exito', 'Estado de recolección actualizado.');
    }
    public function adminEliminar($id)
{
    $solicitud = SolicitudAdopcion::with(['mascota', 'user'])->findOrFail($id);

    // Liberar la mascota si estaba en proceso
    if ($solicitud->mascota && $solicitud->mascota->estado === 'proceso') {
        $solicitud->mascota->update(['estado' => 'disponible']);
    }

    // Solo notificar si el usuario aún existe
    if ($solicitud->user_id && \App\Models\User::find($solicitud->user_id)) {
        Notificacion::create([
            'user_id' => $solicitud->user_id,
            'titulo'  => '🗑️ Solicitud eliminada',
            'mensaje' => 'Tu solicitud de adopción para "' . ($solicitud->mascota->nombre ?? 'la mascota') . '" fue eliminada por el administrador.',
            'tipo'    => 'info',
        ]);
    }

    $solicitud->delete();

    return back()->with('exito', 'Solicitud eliminada correctamente.');
}
// Eliminar cita de adopción
public function eliminarCita($id)
{
    $cita = Cita::with('mascota')->findOrFail($id);

    if ($cita->mascota && $cita->estado === 'programada') {
        $cita->mascota->update(['estado' => 'proceso']);
    }

    $cita->delete();
    return back()->with('exito', 'Cita eliminada correctamente.');
}

// Eliminar recolección de donación
public function eliminarCitaDonacion($id)
{
    $cita = CitaDonacion::with('donacionEspecie')->findOrFail($id);

    if ($cita->donacionEspecie && $cita->estado === 'programada') {
        $cita->donacionEspecie->update(['estado' => 'aprobado']);
    }

    $cita->delete();
    return back()->with('exito', 'Recolección eliminada correctamente.');
}
}