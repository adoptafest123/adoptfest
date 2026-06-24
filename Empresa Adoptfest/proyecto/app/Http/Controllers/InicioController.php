<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAdopcion;
use App\Models\Notificacion;
use App\Models\DonacionDinero;
use App\Models\DonacionEspecie;
use App\Models\User;

class InicioController extends Controller
{
    public function index()
    {
        $solicitudes    = collect();
        $notificaciones = collect();
        $misDonaciones  = collect();
        $noLeidasCount  = 0;
        $usuarioActual  = null;

        if (session('id')) {
            $usuarioActual = User::find(session('id'));

            $solicitudes = SolicitudAdopcion::with(['mascota', 'cita'])
                ->where('user_id', session('id'))
                ->where('oculto_para_usuario', false)
                ->orderBy('created_at', 'desc')
                ->get();

            $donacionesDinero = DonacionDinero::where('user_id', session('id'))
                ->where('oculto_para_usuario', false)
                ->latest()->get()
                ->map(fn($d) => [
                    'id'                    => $d->id,
                    'tipo'                  => 'dinero',
                    'tipo_actividad'        => 'donacion_dinero', // usado en la ruta de ocultar
                    'monto'                 => $d->monto,
                    'estado'                => $d->estado === 'completado' ? 'recibida'
                                             : ($d->estado === 'fallido'   ? 'cancelada' : 'pendiente'),
                    'es_final'              => in_array($d->estado, ['completado', 'fallido']),
                    'created_at'            => $d->created_at,
                    'categoria'             => null,
                    'cantidad'              => null,
                    'descripcion'           => null,
                    'direccion_recoleccion' => null,
                ]);

            $donacionesEspecie = DonacionEspecie::where('user_id', session('id'))
                ->where('oculto_para_usuario', false)
                ->latest()->get()
                ->map(fn($d) => [
                    'id'                    => $d->id,
                    'tipo'                  => 'especie',
                    'tipo_actividad'        => 'donacion_especie',
                    'monto'                 => null,
                    'estado'                => $d->estado === 'confirmado' ? 'recibida'
                                             : ($d->estado === 'rechazado' ? 'cancelada' : 'pendiente'),
                    'es_final'              => in_array($d->estado, ['confirmado', 'rechazado']),
                    'created_at'            => $d->created_at,
                    'categoria'             => $d->categoria,
                    'cantidad'              => $d->cantidad,
                    'descripcion'           => $d->descripcion,
                    'direccion_recoleccion' => $d->direccion_recoleccion,
                ]);

            $misDonaciones = collect($donacionesDinero->toArray())
                ->merge($donacionesEspecie->toArray())
                ->sortByDesc('created_at')
                ->map(fn($d) => (object) $d)
                ->values();

            $notificaciones = Notificacion::where('user_id', session('id'))
                ->orderBy('created_at', 'desc')
                ->get();

            $noLeidasCount = $notificaciones->where('leida', false)->count();
        }

        return view('inicio', compact(
            'solicitudes',
            'notificaciones',
            'misDonaciones',
            'noLeidasCount',
            'usuarioActual'
        ));
    }
}