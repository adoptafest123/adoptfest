<?php

namespace App\Http\Controllers;

use App\Models\DonacionDinero;
use App\Models\DonacionEspecie;
use App\Models\Notificacion;
use App\Models\User;
use App\Services\PayPalService;
use Illuminate\Http\Request;

class DonacionController extends Controller
{
    protected PayPalService $paypal;

    public function __construct(PayPalService $paypal)
    {
        $this->paypal = $paypal;
    }

    public function index()
    {
        if (!session('id')) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para ver esta página.');
        }

        $usuarioActual = User::find(session('id'));

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

        $solicitudes = \App\Models\SolicitudAdopcion::with(['mascota', 'cita'])
            ->where('user_id', session('id'))
            ->where('oculto_para_usuario', false)
            ->latest()->get();

        $notificaciones = Notificacion::where('user_id', session('id'))
            ->latest()->get();

        $noLeidasCount = $notificaciones->where('leida', false)->count();

        return view('donaciones', compact(
            'usuarioActual',
            'misDonaciones',
            'solicitudes',
            'notificaciones',
            'noLeidasCount'
        ));
    }

    // ════════════════════════════════════════
    //  DONACIÓN EN DINERO (PayPal)
    // ════════════════════════════════════════

    public function crearOrdenDinero(Request $request)
    {
        if (!session('id')) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para hacer una donación.');
        }

        $request->validate([
            'monto' => 'required|numeric|min:1|max:10000',
        ], [
            'monto.required' => 'Ingresa un monto a donar.',
            'monto.numeric'  => 'El monto debe ser un número válido.',
            'monto.min'      => 'El monto mínimo de donación es $1.',
            'monto.max'      => 'El monto máximo permitido es $10.000.',
        ]);

        $moneda = config('services.paypal.currency', 'USD');
        $orden  = $this->paypal->crearOrden((float) $request->monto, $moneda);

        if (!$orden || !isset($orden['id'])) {
            return back()->with('error', 'No se pudo conectar con PayPal. Intenta de nuevo en un momento.');
        }

        DonacionDinero::create([
            'user_id'         => session('id'),
            'monto'           => $request->monto,
            'moneda'          => $moneda,
            'paypal_order_id' => $orden['id'],
            'estado'          => 'pendiente',
        ]);

        $linkAprobacion = collect($orden['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$linkAprobacion) {
            return back()->with('error', 'PayPal no devolvió un link de pago válido.');
        }

        return redirect($linkAprobacion);
    }

    public function exitoDinero(Request $request)
    {
        $orderId = $request->query('token');

        if (!$orderId) {
            return redirect('/donaciones')->with('error', 'No se recibió información del pago.');
        }

        $donacion = DonacionDinero::where('paypal_order_id', $orderId)->first();

        if (!$donacion) {
            return redirect('/donaciones')->with('error', 'No encontramos esa donación en nuestros registros.');
        }

        if ($donacion->estado === 'completado') {
            return redirect('/donaciones')->with('exito', '¡Gracias! Tu donación ya había sido confirmada. 🐾');
        }

        $resultado   = $this->paypal->capturarOrden($orderId);
        $pagoExitoso = $resultado && ($resultado['status'] ?? '') === 'COMPLETED';

        if (!$pagoExitoso) {
            $donacion->update(['estado' => 'fallido']);
            return redirect('/donaciones')->with('error', 'El pago no pudo confirmarse. Intenta de nuevo.');
        }

        $puntos = (int) round($donacion->monto);

        $donacion->update([
            'estado'           => 'completado',
            'puntos_otorgados' => $puntos,
        ]);

        $usuario = User::find($donacion->user_id);
        if ($usuario) {
            $usuario->sumarPuntosDonante($puntos);
        }

        return redirect('/donaciones')->with('exito', '¡Gracias por tu donación! 🐾 Tu apoyo ayuda a más mascotas a encontrar hogar.');
    }

    public function canceladoDinero(Request $request)
    {
        $orderId = $request->query('token');

        if ($orderId) {
            DonacionDinero::where('paypal_order_id', $orderId)
                ->where('estado', 'pendiente')
                ->update(['estado' => 'fallido']);
        }

        return redirect('/donaciones')->with('error', 'Cancelaste el proceso de donación. ¡Puedes intentarlo de nuevo cuando quieras!');
    }

    // ════════════════════════════════════════
    //  DONACIÓN EN ESPECIE (insumos)
    // ════════════════════════════════════════

    public function guardarEspecie(Request $request)
    {
        if (!session('id')) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para hacer una donación.');
        }

        $request->validate([
            'categoria'             => 'required|in:alimento,higiene,juguetes,cobijas_camas,medicamentos,otros',
            'especie_destino'       => 'required|in:perro,gato,otro,no_aplica',
            'descripcion'           => 'nullable|string|max:255',
            'cantidad'              => 'required|integer|min:1|max:50',
            'direccion_recoleccion' => 'required|string|min:10|max:255',
            'telefono_contacto'     => 'required|digits_between:7,10',
        ], [
            'categoria.required'               => 'Selecciona qué tipo de insumo vas a donar.',
            'categoria.in'                     => 'Selecciona una categoría válida.',
            'especie_destino.required'         => 'Indica para qué especie es la donación.',
            'especie_destino.in'               => 'Selecciona una especie válida.',
            'cantidad.required'                => 'Indica la cantidad.',
            'cantidad.integer'                 => 'La cantidad debe ser un número entero.',
            'cantidad.min'                     => 'La cantidad mínima es 1.',
            'cantidad.max'                     => 'La cantidad máxima por registro es 50.',
            'direccion_recoleccion.required'   => 'Indica la dirección donde recogeremos la donación.',
            'direccion_recoleccion.min'        => 'Escribe una dirección más detallada (mínimo 10 caracteres).',
            'telefono_contacto.required'       => 'Indica un teléfono de contacto.',
            'telefono_contacto.digits_between' => 'El teléfono debe tener entre 7 y 10 dígitos, solo números.',
        ]);

        DonacionEspecie::create([
            'user_id'               => session('id'),
            'categoria'             => $request->categoria,
            'especie_destino'       => $request->especie_destino,
            'descripcion'           => $request->descripcion,
            'cantidad'              => $request->cantidad,
            'direccion_recoleccion' => $request->direccion_recoleccion,
            'telefono_contacto'     => $request->telefono_contacto,
            'estado'                => 'pendiente',
        ]);

        Notificacion::create([
            'user_id' => session('id'),
            'titulo'  => '📦 Donación registrada',
            'mensaje' => 'Tu donación fue registrada y está pendiente de revisión. Te notificaremos cuándo pasaremos a recogerla.',
            'tipo'    => 'info',
        ]);

        return redirect('/donaciones')->with('exito', '¡Gracias! Registramos tu donación. Un admin la revisará y agendará la recolección.');
    }

    // ════════════════════════════════════════
    //  ADMIN
    // ════════════════════════════════════════

    public function adminIndex()
    {
        $especies = DonacionEspecie::with(['user', 'cita'])->latest()->get();
        $dineros  = DonacionDinero::with('user')->where('estado', 'completado')->latest()->get();

        return view('admin_donaciones', compact('especies', 'dineros'));
    }

    public function aceptarEspecie($id)
    {
        $donacion = DonacionEspecie::findOrFail($id);

        if ($donacion->estado !== 'pendiente') {
            return back()->with('error', 'Esta donación ya fue procesada anteriormente.');
        }

        $donacion->update(['estado' => 'aprobado']);

        return redirect('/admin_citas')->with('exito', 'Donación aprobada. Ahora agenda la cita de recolección.');
    }

    public function rechazarEspecie($id)
    {
        $donacion = DonacionEspecie::findOrFail($id);

        if ($donacion->estado !== 'pendiente') {
            return back()->with('error', 'Esta donación ya fue procesada anteriormente.');
        }

        $donacion->update(['estado' => 'rechazado']);

        Notificacion::create([
            'user_id' => $donacion->user_id,
            'titulo'  => '❌ Donación no pudo procesarse',
            'mensaje' => 'Lamentablemente no pudimos procesar tu donación en este momento. ¡Gracias por tu intención de ayudar!',
            'tipo'    => 'rechazado',
        ]);

        return back()->with('exito', 'Donación marcada como rechazada.');
    }
    public function eliminarEspecie($id)
{
    $donacion = DonacionEspecie::findOrFail($id);

    if ($donacion->user_id && \App\Models\User::find($donacion->user_id)) {
        Notificacion::create([
            'user_id' => $donacion->user_id,
            'titulo'  => '🗑️ Donación eliminada',
            'mensaje' => 'Tu donación de "' . ucfirst(str_replace('_', ' ', $donacion->categoria)) . '" fue eliminada por el administrador.',
            'tipo'    => 'info',
        ]);
    }

    $donacion->delete();

    return back()->with('exito', 'Donación eliminada correctamente.');
}
}