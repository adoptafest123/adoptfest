<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Marcar una notificación como leída
    public function leer($id)
    {
        $noti = Notificacion::where('id', $id)
            ->where('user_id', session('id'))
            ->firstOrFail();

        $noti->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }

    // Eliminar una notificación
    public function eliminar($id)
    {
        $noti = Notificacion::where('id', $id)
            ->where('user_id', session('id'))
            ->firstOrFail();

        $noti->delete();

        return response()->json(['ok' => true]);
    }

    // Marcar todas como leídas
    public function leerTodas()
    {
        Notificacion::where('user_id', session('id'))
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }
    public function ocultarDonacionDinero($id)
{
    \App\Models\DonacionDinero::where('id', $id)
        ->where('user_id', session('id'))
        ->whereIn('estado', ['completado', 'fallido'])
        ->update(['oculto_para_usuario' => true]);

    return response()->json(['ok' => true]);
}

public function ocultarDonacionEspecie($id)
{
    \App\Models\DonacionEspecie::where('id', $id)
        ->where('user_id', session('id'))
        ->whereIn('estado', ['confirmado', 'rechazado'])
        ->update(['oculto_para_usuario' => true]);

    return response()->json(['ok' => true]);
}

public function ocultarAdopcion($id)
{
    \App\Models\SolicitudAdopcion::where('id', $id)
        ->where('user_id', session('id'))
        ->whereIn('estado', ['aceptada', 'rechazada'])
        ->update(['oculto_para_usuario' => true]);

    return response()->json(['ok' => true]);
}
}