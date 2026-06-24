<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\SolicitudAdopcion;
use App\Models\Notificacion;
use App\Models\DonacionDinero;
use App\Models\DonacionEspecie;
use App\Models\User;
use Carbon\Carbon;

class EventoController extends Controller
{
    // ── Mueve imagen a storage
    private function moverImagen(Request $request): ?string
    {
        if (!$request->hasFile('imagen')) return null;

        $archivo      = $request->file('imagen');
        $nombreImagen = time() . '_' . $archivo->getClientOriginalName();
        $destino      = storage_path('app/public/img');

        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $archivo->move($destino, $nombreImagen);
        return $nombreImagen;
    }

    // ── Elimina imagen del storage
    private function eliminarImagen(?string $imagen): void
    {
        if (!$imagen) return;
        $ruta = storage_path('app/public/img/' . $imagen);
        if (file_exists($ruta)) unlink($ruta);
    }

    // ── Vista pública de eventos
    public function index()
    {
        $eventos = Evento::orderBy('fecha', 'desc')->get();

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
                    'tipo_actividad'        => 'donacion_dinero',
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

        return view('eventos', compact(
            'eventos',
            'solicitudes',
            'notificaciones',
            'misDonaciones',
            'noLeidasCount',
            'usuarioActual'
        ));
    }

    // ── Vista admin de eventos
    public function admin(Request $request)
    {
        $query = Evento::query();

        if ($request->buscar) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%'.$request->buscar.'%')
                  ->orWhere('lugar',  'like', '%'.$request->buscar.'%');
            });
        }

        if ($request->estado)    $query->where('estado',    $request->estado);
        if ($request->categoria) $query->where('categoria', $request->categoria);

        $eventos = $query->orderBy('fecha', 'desc')->get();
        return view('admin_eventos', compact('eventos'));
    }

    // ── CREAR evento
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|min:3|max:200',
            'fecha'       => 'required|date',
            'lugar'       => 'nullable|max:100',
            'descripcion' => 'nullable|max:500',
            'categoria'   => 'required|in:adopcion,educacion,recreacion',
            'imagen'      => 'nullable|image|max:5120',
        ]);

        $datos = [
            'titulo'      => $request->titulo,
            'fecha'       => $request->fecha,
            'lugar'       => $request->lugar,
            'descripcion' => $request->descripcion,
            'categoria'   => $request->categoria,
            'estado'      => 'activo',
        ];

        $imagen = $this->moverImagen($request);
        if ($imagen) $datos['imagen'] = $imagen;

        Evento::create($datos);
        return redirect('/admin_eventos')->with('exito', 'Evento creado correctamente.');
    }

    // ── EDITAR evento
    public function update(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);

        $request->validate([
            'titulo'      => 'required|min:3|max:200',
            'fecha'       => 'required|date',
            'lugar'       => 'nullable|max:100',
            'descripcion' => 'nullable|max:500',
            'categoria'   => 'required|in:adopcion,educacion,recreacion',
            'estado'      => 'required|in:activo,finalizado,cancelado',
            'imagen'      => 'nullable|image|max:5120',
        ]);

        $datos = [
            'titulo'      => $request->titulo,
            'fecha'       => Carbon::parse($request->fecha),
            'lugar'       => $request->lugar,
            'descripcion' => $request->descripcion,
            'categoria'   => $request->categoria,
            'estado'      => $request->estado,
        ];

        if ($request->hasFile('imagen')) {
            $this->eliminarImagen($evento->imagen);
            $imagen = $this->moverImagen($request);
            if ($imagen) $datos['imagen'] = $imagen;
        }

        $evento->update($datos);
        return redirect('/admin_eventos')->with('exito', 'Evento actualizado correctamente.');
    }

    // ── ELIMINAR evento
    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $this->eliminarImagen($evento->imagen);
        $evento->delete();
        return redirect('/admin_eventos')->with('exito', 'Evento eliminado correctamente.');
    }
}