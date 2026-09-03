<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mascota;
use App\Models\SolicitudAdopcion;
use App\Models\Notificacion;
use App\Models\DonacionDinero;
use App\Models\DonacionEspecie;
use App\Models\User;

class MascotaController extends Controller
{
    // ── Genera código único
    private function generarCodigo(): string
    {
        do {
            $letras  = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 3));
            $numeros = rand(100, 999);
            $codigo  = "$letras-$numeros";
        } while (Mascota::where('codigo', $codigo)->exists());

        return $codigo;
    }

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

    // ── Vista pública de adopción
    public function index()
    {
        $mascotas = Mascota::all();

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

        return view('adopcion', compact(
            'mascotas',
            'solicitudes',
            'notificaciones',
            'misDonaciones',
            'noLeidasCount',
            'usuarioActual'
        ));
    }

    // ── Vista admin de mascotas
    public function admin(Request $request)
    {
        $query = Mascota::query();

        if ($request->buscar) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $request->buscar . '%');
            });
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        $mascotas = $query->orderBy('created_at', 'desc')->get();
        return view('admin_mascotas', compact('mascotas'));
    }

    // ── CREAR mascota
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|min:2|max:100',
            'tipo'        => 'required|in:perro,gato',
            'edad'        => 'required|integer|min:1|max:5',
            'descripcion' => 'required|max:255',
            'historia'    => 'nullable|max:1000',
            'imagen'      => 'nullable|image|max:2048',
        ]);

        $datos = [
            'nombre'      => $request->nombre,
            'tipo'        => $request->tipo,
            'edad'        => $request->edad,
            'descripcion' => $request->descripcion,
            'historia'    => $request->historia,
            'estado'      => 'disponible',
            'codigo'      => $this->generarCodigo(),
        ];

        $imagen = $this->moverImagen($request);
        if ($imagen) $datos['imagen'] = $imagen;

        Mascota::create($datos);
        return redirect('/admin_mascotas')->with('exito', 'Mascota registrada correctamente.');
    }

    // ── EDITAR mascota
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|min:2|max:100',
            'tipo'        => 'required|in:perro,gato',
            'edad'        => 'required|integer|min:1|max:5',
            'descripcion' => 'required|max:255',
            'historia'    => 'nullable|max:1000',
            'estado'      => 'required|in:disponible,proceso,evento',
            'imagen'      => 'nullable|image|max:2048',
        ]);

        $mascota = Mascota::findOrFail($id);

        $datos = [
            'nombre'      => $request->nombre,
            'tipo'        => $request->tipo,
            'edad'        => $request->edad,
            'descripcion' => $request->descripcion,
            'historia'    => $request->historia,
            'estado'      => $request->estado,
        ];

        $imagen = $this->moverImagen($request);
        if ($imagen) $datos['imagen'] = $imagen;

        $mascota->update($datos);
        return redirect('/admin_mascotas')->with('exito', 'Mascota actualizada correctamente.');
    }

    // ── ELIMINAR mascota
    public function destroy($id)
    {
        Mascota::destroy($id);
        return redirect('/admin_mascotas')->with('exito', 'Mascota eliminada correctamente.');
    }
}