<?php

use App\Http\Controllers\MascotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscripcionEventoController;

Route::get('/login', function () { return view('login');});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/registro', function () {  return view('registro');});

Route::post('/registro', [AuthController::class, 'registro']);

use App\Http\Controllers\InicioController;

Route::get('/inicio', [InicioController::class, 'index']);
Route::get('/',       [InicioController::class, 'index']);

Route::get('/eventos', function () {return view('eventos');});

Route::get('/adopcion', [MascotaController::class, 'index']);

Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/admin', function () { if(session('rol') != 'admin'){ return redirect('/'); } return view('admin');});

Route::resource('admin', UserController::class);

Route::get('/admin_mascotas', [MascotaController::class, 'admin']);
Route::post('/admin_mascotas', [MascotaController::class, 'store']);
Route::put('/admin_mascotas/{id}', [MascotaController::class, 'update']);
Route::delete('/admin_mascotas/{id}', [MascotaController::class, 'destroy']);

use App\Http\Controllers\EventoController;

Route::get('/admin_eventos', [EventoController::class, 'admin'])->name('admin_eventos.index');

Route::post('/admin_eventos', [EventoController::class, 'store'])->name('admin_eventos.store');

Route::put('/admin_eventos/{id}', [EventoController::class, 'update'])->name('admin_eventos.update');

Route::delete('/admin_eventos/{id}', [EventoController::class, 'destroy'])->name('admin_eventos.destroy');

Route::get('/eventos', [EventoController::class, 'index']); // vista usuarios

Route::get('/mi-perfil', [ UserController::class, 'perfil']);
Route::post('/perfil/actualizar',[UserController::class,'actualizarPerfil'])->name('perfil.actualizar');


// Formulario de inscripción 
Route::get('/evento/{id}/formulario_eventos',  [InscripcionEventoController::class, 'formulario']);
Route::post('/evento/{id}/formulario_eventos', [InscripcionEventoController::class, 'guardar']);

// Panel admin de inscripciones
Route::get('/admin_inscripciones',              [InscripcionEventoController::class, 'adminIndex']);
Route::post('/admin_inscripciones/{id}',        [InscripcionEventoController::class, 'adminActualizar']);
Route::delete('/admin_inscripciones/{id}', [InscripcionEventoController::class, 'adminEliminar']);

use App\Http\Controllers\NotificacionController;

// Notificaciones
Route::post('/notificaciones/{id}/leer',      [NotificacionController::class, 'leer']);
Route::delete('/notificaciones/{id}/eliminar', [NotificacionController::class, 'eliminar']);
Route::post('/notificaciones/leer-todas',      [NotificacionController::class, 'leerTodas']);

use App\Http\Controllers\AdopcionController;
// Adopción
Route::get('/adopcion/{id}/formulario',    [AdopcionController::class, 'formulario'])->name('adopcion.formulario');
Route::post('/adopcion/{id}/solicitud',    [AdopcionController::class, 'guardar'])->name('adopcion.guardar');

// Admin solicitudes
Route::get('/admin_adopciones',            [AdopcionController::class, 'adminIndex'])->name('admin.adopciones');
Route::post('/admin_adopciones/{id}/responder', [AdopcionController::class, 'responder'])->name('admin.adopciones.responder');
Route::delete('/admin_adopciones/{id}', [AdopcionController::class, 'adminEliminar']);

// Admin citas
Route::get('/admin_citas',                 [AdopcionController::class, 'adminCitas'])->name('admin.citas');
Route::post('/admin_citas/{id}/agendar',   [AdopcionController::class, 'agendar'])->name('admin.citas.agendar');
Route::post('/admin_citas/donacion/{id}/estado', [AdopcionController::class, 'cambiarEstadoCitaDonacion']) ->name('admin_citas.donacion.estado');
Route::post('/admin_citas/{id}/estado',    [AdopcionController::class, 'cambiarEstadoCita'])->name('admin.citas.estado');
Route::delete('/admin_citas/{id}',          [AdopcionController::class, 'eliminarCita']);
Route::delete('/admin_citas/donacion/{id}', [AdopcionController::class, 'eliminarCitaDonacion']);

use App\Http\Controllers\DonacionController;

// ── Cliente ──
Route::get('/donaciones', [DonacionController::class, 'index'])->name('donaciones.index');

// Dinero (PayPal)
Route::post('/donaciones/dinero', [DonacionController::class, 'crearOrdenDinero'])->name('donaciones.dinero.crear');
Route::get('/donaciones/dinero/exito', [DonacionController::class, 'exitoDinero'])->name('donaciones.paypal.exito');
Route::get('/donaciones/dinero/cancelado', [DonacionController::class, 'canceladoDinero'])->name('donaciones.paypal.cancelado');

// Especie (insumos)
Route::post('/donaciones/especie', [DonacionController::class, 'guardarEspecie'])->name('donaciones.especie.guardar');

// ── Admin: Donaciones ──
Route::get('/admin_donaciones', [DonacionController::class, 'adminIndex'])->name('admin_donaciones.index');
Route::post('/admin_donaciones/especie/{id}/aceptar', [DonacionController::class, 'aceptarEspecie'])->name('admin_donaciones.especie.aceptar');
Route::post('/admin_donaciones/especie/{id}/rechazar', [DonacionController::class, 'rechazarEspecie'])->name('admin_donaciones.especie.rechazar');
Route::delete('/admin_donaciones/especie/{id}', [DonacionController::class, 'eliminarEspecie']);

Route::post('/admin_citas/donacion/{id}/agendar', [AdopcionController::class, 'agendarRecoleccion'])->name('admin_citas.donacion.agendar');

Route::post('/actividad/donacion-dinero/{id}/ocultar',  [NotificacionController::class, 'ocultarDonacionDinero']);
Route::post('/actividad/donacion-especie/{id}/ocultar', [NotificacionController::class, 'ocultarDonacionEspecie']);
Route::post('/actividad/adopcion/{id}/ocultar',         [NotificacionController::class, 'ocultarAdopcion']);