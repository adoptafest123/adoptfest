<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Donaciones - Adoptafest</title>
    <link rel="stylesheet" href="{{ asset('css/donaciones.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-principal fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/inicio">
            <img src="{{ asset('storage/img/logo de empresa.png') }}" alt="Logo" class="logo-navbar">
            <span class="ms-2 fw-bold">Adoptafest</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="/eventos">Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="/adopcion">Adoptar</a></li>
                <li class="nav-item"><a class="nav-link" href="/donaciones">Donar</a></li>
                <li class="nav-item ms-3">
                    @if(session('nombre'))
                        <div class="estilo-bienvenida">
                            @if(session('rol') == 'admin')
                                <a href="/admin" class="btn-admin">admin</a>
                            @endif

                            @php $noLeidas = $noLeidasCount ?? 0; @endphp
                            <button class="btn-perfil"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPerfil"
                                    style="position:relative;">
                                @if(session('foto'))
                                    <img src="{{ asset('storage/img/perfiles/' . session('foto')) }}"
                                         class="avatar-perfil"
                                         style="object-fit:cover;"
                                         alt="foto">
                                @else
                                    <div class="avatar-perfil">
                                        {{ strtoupper(substr(session('nombre'), 0, 1)) }}
                                    </div>
                                @endif
                                <span>{{ session('nombre') }}</span>
                                @if($noLeidas > 0)
                                    <span class="badge-noti">{{ $noLeidas }}</span>
                                @endif
                            </button>

                            <a href="/logout" class="btn btn-danger boton-logout">Cerrar Sesión</a>
                        </div>
                    @else
                        <a href="/login" class="btn btn-warning">Login</a>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="hero-donaciones">
    <h1>💚 Haz una donación</h1>
    <p>Cada aporte ayuda a que más mascotas encuentren un hogar</p>
</div>

<!-- FORMULARIO -->
<div class="container" style="max-width: 720px;">

    @if(session('exito'))
        <div class="alert alert-success rounded-3 mb-3">✅ {{ session('exito') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-3">❌ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- TABS -->
    <div class="tabs-donacion">
        <button class="tab-btn activo" data-tab="dinero" onclick="cambiarTab('dinero', this)">
            💵 Donar dinero
        </button>
        <button class="tab-btn" data-tab="especie" onclick="cambiarTab('especie', this)">
            📦 Donar insumos
        </button>
    </div>

    <!-- TAB: DINERO -->
    <div class="tab-contenido" id="tab-dinero">
        <div class="donacion-card">
            <h5 class="mb-1">💵 Donación en dinero</h5>
            <p class="text-muted small mb-4">El pago se procesa de forma segura a través de PayPal.</p>

            <div id="alerta-dinero" class="alert alert-danger rounded-3 mb-3" style="display:none;">
                <strong>⚠️ Faltan los siguientes campos:</strong>
                <ul id="lista-errores-dinero" class="mb-0 mt-1"></ul>
            </div>

            <form method="POST" action="{{ route('donaciones.dinero.crear') }}" id="formDinero" novalidate>
                @csrf
                <label class="form-label fw-semibold">Elige un monto (USD)</label>
                <div class="montos-rapidos mb-3">
                    <button type="button" class="monto-chip" onclick="elegirMonto(5)">$5</button>
                    <button type="button" class="monto-chip" onclick="elegirMonto(10)">$10</button>
                    <button type="button" class="monto-chip" onclick="elegirMonto(25)">$25</button>
                    <button type="button" class="monto-chip" onclick="elegirMonto(50)">$50</button>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text">$</span>
                    <input type="text" name="monto" id="monto" class="form-control"
                           placeholder="Otro monto" inputmode="decimal" maxlength="10">
                </div>
                <span class="error-msg" id="err_monto">⚠️ Ingresa un monto válido (mínimo $1, máximo $10.000).</span>
                <span class="form-text text-muted d-block mb-1">Monto mínimo $1 USD, máximo $10.000 USD.</span>
                <button type="submit" class="btn-donar mt-4">
                    Donar ahora con PayPal 💳
                </button>
            </form>
        </div>
    </div>

    <!-- TAB: INSUMOS -->
    <div class="tab-contenido d-none" id="tab-especie">
        <div class="donacion-card">
            <h5 class="mb-1">📦 Donación en insumos</h5>
            <p class="text-muted small mb-4">
                Registra qué vas a donar. Un repartidor pasará a recogerlo en la dirección que indiques.
            </p>

            <div id="alerta-especie" class="alert alert-danger rounded-3 mb-3" style="display:none;">
                <strong>⚠️ Faltan los siguientes campos:</strong>
                <ul id="lista-errores-especie" class="mb-0 mt-1"></ul>
            </div>

            <form method="POST" action="{{ route('donaciones.especie.guardar') }}" id="formEspecie" novalidate>
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria" id="esp_categoria" class="form-select">
                            <option value="">Selecciona</option>
                            <option value="alimento">🍖 Alimento</option>
                            <option value="higiene">🧼 Higiene</option>
                            <option value="juguetes">🧸 Juguetes</option>
                            <option value="cobijas_camas">🛏️ Cobijas / Camas</option>
                            <option value="otros">📦 Otros</option>
                        </select>
                        <span class="error-msg" id="err_esp_categoria">⚠️ Selecciona una categoría.</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">¿Para qué mascota? <span class="text-danger">*</span></label>
                        <select name="especie_destino" id="esp_especie" class="form-select">
                            <option value="">Selecciona</option>
                            <option value="perro">🐶 Perro</option>
                            <option value="gato">🐱 Gato</option>
                        </select>
                        <span class="error-msg" id="err_esp_especie">⚠️ Selecciona una opción.</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                    <div class="stepper-cantidad">
                        <button type="button" class="stepper-btn" id="btn_menos" onclick="cambiarCantidad(-1)">−</button>
                        <input type="text" name="cantidad" id="esp_cantidad" class="form-control text-center"
                               value="1" readonly>
                        <button type="button" class="stepper-btn" id="btn_mas" onclick="cambiarCantidad(1)">+</button>
                    </div>
                    <span class="form-text text-muted">Mínimo 1, máximo 50.</span>
                    <span class="error-msg" id="err_esp_cantidad">⚠️ Ingresa una cantidad válida (entre 1 y 50).</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                    <textarea name="descripcion" id="esp_descripcion" class="form-control" rows="2"
                              maxlength="255" minlength="5"
                              placeholder="Ej: 2 bultos de concentrado marca X para perro adulto"></textarea>
                    <span class="form-text text-muted">Mínimo 5 caracteres.</span>
                    <span class="error-msg" id="err_esp_descripcion">⚠️ La descripción es obligatoria (mínimo 5 caracteres).</span>
                </div>
                <div class="section-divider"><span>📍 Datos de recolección</span></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Dirección de recolección <span class="text-danger">*</span></label>
                    <input type="text" name="direccion_recoleccion" id="esp_direccion" class="form-control"
                           placeholder="Ej: Calle 45 #12-30, Apto 301, Bogotá" maxlength="255">
                    <span class="form-text text-muted">Mínimo 10 caracteres.</span>
                    <span class="error-msg" id="err_esp_direccion">⚠️ Escribe una dirección completa (mínimo 10 caracteres).</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Teléfono de contacto <span class="text-danger">*</span></label>
                    <input type="text" name="telefono_contacto" id="esp_telefono" class="form-control"
                           inputmode="numeric" maxlength="10"
                           placeholder="Ej: 3001234567"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <span class="form-text text-muted">Solo números, entre 7 y 10 dígitos.</span>
                    <span class="error-msg" id="err_esp_telefono">⚠️ Ingresa un teléfono válido (7 a 10 dígitos).</span>
                </div>
                <div class="alert alert-success border-0 small py-2 mb-3">
                    ℹ️ Tu donación quedará <strong>pendiente de revisión</strong>. Te notificaremos cuándo
                    pasaremos a recogerla.
                </div>
                <button type="submit" class="btn-donar mt-2">
                    Registrar donación 📦
                </button>
            </form>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer-principal text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h4>🐾 Adoptafest</h4>
                <p>Ayudamos a animales a encontrar hogar.</p>
            </div>
            <div class="col-md-4">
                <h5>Enlaces</h5>
                <ul class="list-unstyled">
                    <li><a href="/inicio" class="text-white text-decoration-none">Inicio</a></li>
                    <li><a href="/adopcion" class="text-white text-decoration-none">Mascotas</a></li>
                    <li><a href="/adopcion" class="text-white text-decoration-none">Adoptar</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contacto</h5>
                <p>Bogotá, Colombia</p>
                <p>+57 363 290 0392</p>
            </div>
        </div>
        <hr>
        <p class="text-center">© 2026 Adoptafest</p>
    </div>
</footer>

{{-- ══ MODAL PERFIL ══ --}}
@if(session('nombre'))
@php
    $notificaciones = $notificaciones ?? collect();
    $noLeidasCount  = $noLeidasCount  ?? 0;

    $puntos         = $usuarioActual->puntos_donante ?? 0;
    $nivelActual    = $usuarioActual->nivelDonante()    ?? 'apoyo';
    $insigniaActual = $usuarioActual->insigniaDonante() ?? ['emoji'=>'🌱','etiqueta'=>'Apoyo','color'=>'#16a34a'];

    $nivelesOrden = [
        'apoyo'  => ['min' => 0,    'label' => 'Apoyo',          'emoji' => '🌱', 'color' => '#16a34a'],
        'bronce' => ['min' => 200,  'label' => 'Donante Bronce', 'emoji' => '🥉', 'color' => '#b45309'],
        'plata'  => ['min' => 500,  'label' => 'Donante Plata',  'emoji' => '🥈', 'color' => '#9ca3af'],
        'oro'    => ['min' => 1000, 'label' => 'Donante Oro',    'emoji' => '🥇', 'color' => '#d4af37'],
    ];

    $nivelesKeys  = array_keys($nivelesOrden);
    $idxActual    = array_search($nivelActual, $nivelesKeys);
    $esTope       = ($nivelActual === 'oro');
    $nivelSig     = $esTope ? null : $nivelesOrden[$nivelesKeys[$idxActual + 1]];
    $puntosFaltan = $esTope ? 0 : $nivelSig['min'] - $puntos;

    $minActual = $nivelesOrden[$nivelActual]['min'];
    $maxActual = $esTope ? 1000 : $nivelSig['min'];
    $rango     = $maxActual - $minActual;
    $pct       = $rango > 0 ? min(100, round(($puntos - $minActual) / $rango * 100)) : 100;

    $beneficios = [
        'apoyo'  => ['Acceso a eventos generales', 'Reconocimiento en comunidad'],
        'bronce' => ['Acceso a eventos generales', 'Reconocimiento en comunidad', 'Prioridad en inscripciones', 'Badge exclusivo Bronce'],
        'plata'  => ['Acceso a eventos generales', 'Reconocimiento en comunidad', 'Prioridad en inscripciones', 'Badge exclusivo Plata', 'Acceso anticipado a nuevas mascotas', 'Mención especial en eventos'],
        'oro'    => ['Acceso a eventos generales', 'Reconocimiento en comunidad', 'Prioridad en inscripciones', 'Badge exclusivo Oro', 'Acceso anticipado a nuevas mascotas', 'Mención especial en eventos', 'Certificado de Donante Destacado', 'Atención preferencial del equipo'],
    ];
    $beneficiosActuales = $beneficios[$nivelActual];
@endphp

<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content perfil-modal">

            <!-- HEADER -->
            <div class="perfil-header">
                @if(session('foto'))
                    <img src="{{ asset('storage/img/perfiles/' . session('foto')) }}"
                         class="rounded-circle" width="90" height="90"
                         style="object-fit:cover; border:3px solid white; flex-shrink:0;">
                @else
                    <div class="perfil-avatar" style="flex-shrink:0;">
                        {{ strtoupper(substr(session('nombre'), 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h3 class="mb-0">{{ session('nombre') }}</h3>
                    <small style="opacity:0.85;">{{ session('email') }}</small>
                    <div class="mt-1">
                        <span style="
                            background: {{ $insigniaActual['color'] }}30;
                            color: {{ $insigniaActual['color'] }};
                            border: 1px solid {{ $insigniaActual['color'] }}60;
                            border-radius: 20px; padding: 2px 10px;
                            font-size: .78rem; font-weight: 700;">
                            {{ $insigniaActual['emoji'] }} {{ $insigniaActual['etiqueta'] }}
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">
                <div class="row g-4">

                    <!-- COLUMNA 1: FORMULARIO + NIVEL DONANTE -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h5>👤 Información Personal</h5>
                                <hr>
                                <form action="{{ route('perfil.actualizar') }}"
                                      method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="text-center mb-4">
                                        @if(session('foto'))
                                            <img src="{{ asset('storage/img/perfiles/' . session('foto')) }}"
                                                 width="80" height="80" class="rounded-circle"
                                                 style="object-fit:cover; border:3px solid #198754;">
                                        @else
                                            <div class="perfil-avatar mx-auto mb-1"
                                                 style="width:80px;height:80px;font-size:32px;">
                                                {{ strtoupper(substr(session('nombre'), 0, 1)) }}
                                            </div>
                                        @endif
                                        <p class="text-muted small mt-1 mb-0">Foto actual</p>
                                    </div>
                                    <label class="form-label fw-semibold">Nombre</label>
                                    <input type="text" name="name" class="form-control mb-3"
                                           value="{{ session('nombre') }}" required>
                                    <label class="form-label fw-semibold">Correo</label>
                                    <input type="email" name="email" class="form-control mb-3"
                                           value="{{ session('email') }}" required>
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" name="telefono" class="form-control mb-3"
                                           value="{{ session('telefono') }}" placeholder="Número de teléfono">
                                    <label class="form-label fw-semibold">Descripción</label>
                                    <textarea name="descripcion" rows="3" class="form-control mb-3"
                                              placeholder="Cuéntanos algo sobre ti...">{{ session('descripcion') }}</textarea>
                                    <label class="form-label fw-semibold">Nueva foto de perfil</label>
                                    <input type="file" name="foto" accept="image/*" class="form-control mb-3">
                                    <label class="form-label fw-semibold">Nueva contraseña</label>
                                    <input type="password" name="password" class="form-control mb-3"
                                           placeholder="Dejar vacío para no cambiar">
                                    <button type="submit" class="btn btn-success w-100 mt-2">
                                        💾 Guardar cambios
                                    </button>
                                </form>

                                <!-- TARJETA NIVEL DONANTE -->
                                <div style="
                                    margin-top:24px; border-radius:16px;
                                    border:2px solid {{ $insigniaActual['color'] }}50;
                                    background:linear-gradient(135deg,{{ $insigniaActual['color'] }}10,{{ $insigniaActual['color'] }}05);
                                    padding:18px;">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                                        <span style="font-size:2rem; line-height:1;">{{ $insigniaActual['emoji'] }}</span>
                                        <div>
                                            <div style="font-weight:700; font-size:.95rem; color:{{ $insigniaActual['color'] }};">
                                                {{ $insigniaActual['etiqueta'] }}
                                            </div>
                                            <div style="font-size:.78rem; color:#6b7280;">
                                                {{ $puntos }} punto{{ $puntos != 1 ? 's' : '' }} acumulado{{ $puntos != 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <div style="display:flex; justify-content:space-between; font-size:.72rem; color:#6b7280; margin-bottom:5px;">
                                            <span>{{ $nivelesOrden[$nivelActual]['emoji'] }} {{ $nivelesOrden[$nivelActual]['label'] }}</span>
                                            @if(!$esTope)
                                                <span>{{ $nivelSig['emoji'] }} {{ $nivelSig['label'] }}</span>
                                            @else
                                                <span style="color:{{ $insigniaActual['color'] }}; font-weight:700;">🏆 Nivel máximo</span>
                                            @endif
                                        </div>
                                        <div style="background:#e5e7eb; border-radius:999px; height:10px; overflow:hidden;">
                                            <div style="
                                                height:100%; width:{{ $pct }}%;
                                                background:linear-gradient(90deg,{{ $insigniaActual['color'] }},{{ $insigniaActual['color'] }}bb);
                                                border-radius:999px; transition:width .6s ease;">
                                            </div>
                                        </div>
                                        @if(!$esTope)
                                            <div style="font-size:.72rem; color:#6b7280; margin-top:5px; text-align:center;">
                                                Te faltan <strong style="color:{{ $insigniaActual['color'] }};">{{ $puntosFaltan }} puntos</strong>
                                                para alcanzar <strong>{{ $nivelSig['label'] }}</strong>
                                            </div>
                                        @else
                                            <div style="font-size:.72rem; color:{{ $insigniaActual['color'] }}; margin-top:5px; text-align:center; font-weight:700;">
                                                🎉 ¡Has alcanzado el nivel máximo!
                                            </div>
                                        @endif
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-top:12px; margin-bottom:14px;">
                                        @foreach($nivelesOrden as $clave => $info)
                                        <div style="text-align:center; flex:1;">
                                            <div style="
                                                font-size:{{ $clave === $nivelActual ? '1.4rem' : '1rem' }};
                                                filter:{{ $clave === $nivelActual ? 'none' : 'grayscale(60%) opacity(0.5)' }};
                                                transition:all .3s;">{{ $info['emoji'] }}</div>
                                            <div style="
                                                font-size:.6rem;
                                                font-weight:{{ $clave === $nivelActual ? '700' : '400' }};
                                                color:{{ $clave === $nivelActual ? $info['color'] : '#9ca3af' }};
                                                margin-top:2px;">{{ $info['min'] }}p</div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div style="border-top:1px solid {{ $insigniaActual['color'] }}30; padding-top:12px;">
                                        <div style="font-size:.78rem; font-weight:700; color:#374151; margin-bottom:8px;">
                                            ✨ Tus beneficios actuales
                                        </div>
                                        @foreach($beneficiosActuales as $b)
                                        <div style="display:flex; align-items:center; gap:6px; font-size:.75rem; color:#374151; margin-bottom:5px;">
                                            <span style="color:{{ $insigniaActual['color'] }}; font-size:.65rem;">●</span>
                                            {{ $b }}
                                        </div>
                                        @endforeach
                                        @if(!$esTope)
                                        <div style="margin-top:10px; font-size:.72rem; color:#9ca3af;">
                                            Sube a <strong style="color:{{ $nivelSig['color'] }};">{{ $nivelSig['emoji'] }} {{ $nivelSig['label'] }}</strong>
                                            y desbloquea más beneficios donando.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <!-- FIN TARJETA NIVEL -->

                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: MIS ACTIVIDADES -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h5>📋 Mis Actividades</h5>
                                <hr>

                                {{-- DONACIONES --}}
                                <div class="mb-2" style="font-size:.8rem; font-weight:700; color:#16a34a; letter-spacing:.5px;">
                                    💚 DONACIONES
                                </div>

                                @if($misDonaciones->count())
                                    <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
                                        @foreach($misDonaciones as $don)
                                        @php
                                            $borde    = $don->estado === 'recibida'  ? '#bbf7d0' : ($don->estado === 'cancelada' ? '#fecaca' : '#e5e7eb');
                                            $fondo    = $don->estado === 'recibida'  ? '#f0fdf4' : ($don->estado === 'cancelada' ? '#fef2f2' : '#f9fafb');
                                            $bgBadge  = $don->estado === 'recibida'  ? '#dcfce7' : ($don->estado === 'cancelada' ? '#fee2e2' : '#fef9c3');
                                            $colBadge = $don->estado === 'recibida'  ? '#166534' : ($don->estado === 'cancelada' ? '#991b1b' : '#854d0e');
                                        @endphp
                                        <div class="actividad-item"
                                             id="actividad-{{ $don->tipo_actividad }}-{{ $don->id }}"
                                             style="border-radius:12px; padding:12px 14px;
                                                    border:1.5px solid {{ $borde }}; background:{{ $fondo }};">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div style="font-size:1.4rem;">
                                                    {{ $don->tipo === 'dinero' ? '💵' : '📦' }}
                                                </div>
                                                <div style="flex:1;">
                                                    <div style="font-weight:700; font-size:.85rem; color:#111827;">
                                                        @if($don->tipo === 'dinero')
                                                            ${{ number_format($don->monto, 2) }} USD
                                                        @else
                                                            {{ ucfirst($don->categoria ?? 'Insumo') }} × {{ $don->cantidad }}
                                                        @endif
                                                    </div>
                                                    <div style="font-size:.72rem; color:#6b7280;">
                                                        {{ \Carbon\Carbon::parse($don->created_at)->diffForHumans() }}
                                                    </div>
                                                </div>
                                                <span style="border-radius:20px; padding:2px 8px; font-size:.68rem;
                                                             font-weight:700; white-space:nowrap;
                                                             background:{{ $bgBadge }}; color:{{ $colBadge }};">
                                                    @if($don->estado === 'recibida') ✅ Recibida
                                                    @elseif($don->estado === 'cancelada') ❌ Cancelada
                                                    @else ⏳ Pendiente
                                                    @endif
                                                </span>
                                            </div>
                                            @if($don->tipo === 'especie' && $don->direccion_recoleccion)
                                                <div style="font-size:.75rem; color:#374151; margin-top:5px;">
                                                    📍 {{ $don->direccion_recoleccion }}
                                                </div>
                                            @endif
                                            @if($don->descripcion)
                                                <div style="font-size:.75rem; color:#6b7280; margin-top:3px; font-style:italic;">
                                                    💬 {{ $don->descripcion }}
                                                </div>
                                            @endif

                                            @if($don->es_final)
                                            <button
                                                onclick="eliminarActividad('{{ $don->tipo_actividad }}', {{ $don->id }}, this)"
                                                style="background:none; border:none; color:#9ca3af;
                                                       font-size:.72rem; padding:0; margin-top:6px;
                                                       cursor:pointer; text-decoration:underline; display:block;">
                                                🗑 Quitar de mi historial
                                            </button>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted mb-4" style="font-size:.85rem;">
                                        <p class="mb-1">💚</p>
                                        <p class="mb-0">Aún no has hecho donaciones</p>
                                    </div>
                                @endif

                                <div style="border-top:1px solid #e5e7eb; margin:8px 0 14px;"></div>

                                {{-- ADOPCIONES --}}
                                <div class="mb-2" style="font-size:.8rem; font-weight:700; color:#16a34a; letter-spacing:.5px;">
                                    🐾 ADOPCIONES
                                </div>

                                @if(isset($solicitudes) && $solicitudes->count())
                                    <div style="display:flex; flex-direction:column; gap:10px;">
                                        @foreach($solicitudes as $sol)
                                        @php
                                            $borde    = $sol->estado === 'aceptada'  ? '#bbf7d0' : ($sol->estado === 'rechazada' ? '#fecaca' : '#e5e7eb');
                                            $fondo    = $sol->estado === 'aceptada'  ? '#f0fdf4' : ($sol->estado === 'rechazada' ? '#fef2f2' : '#f9fafb');
                                            $bgBadge  = $sol->estado === 'aceptada'  ? '#dcfce7' : ($sol->estado === 'rechazada' ? '#fee2e2' : '#fef9c3');
                                            $colBadge = $sol->estado === 'aceptada'  ? '#166534' : ($sol->estado === 'rechazada' ? '#991b1b' : '#854d0e');
                                        @endphp
                                        <div class="actividad-item"
                                             id="actividad-adopcion-{{ $sol->id }}"
                                             style="border-radius:12px; padding:12px 14px;
                                                    border:1.5px solid {{ $borde }}; background:{{ $fondo }};">
                                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                                @if($sol->mascota && $sol->mascota->imagen)
                                                    <img src="{{ asset('storage/img/' . $sol->mascota->imagen) }}"
                                                         style="width:38px; height:38px; border-radius:50%;
                                                                object-fit:cover; border:2px solid #16a34a; flex-shrink:0;"
                                                         onerror="this.style.display='none'">
                                                @else
                                                    <div style="width:38px; height:38px; border-radius:50%; background:#dcfce7;
                                                                display:flex; align-items:center; justify-content:center;
                                                                font-size:1.2rem; flex-shrink:0;">🐾</div>
                                                @endif
                                                <div style="flex:1; min-width:0;">
                                                    <div style="font-weight:700; font-size:.85rem; color:#111827;">
                                                        {{ $sol->mascota->nombre ?? 'Mascota' }}
                                                    </div>
                                                    <div style="font-size:.72rem; color:#6b7280;">
                                                        Enviada {{ $sol->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                                <span style="border-radius:20px; padding:2px 8px; font-size:.68rem;
                                                             font-weight:700; white-space:nowrap;
                                                             background:{{ $bgBadge }}; color:{{ $colBadge }};">
                                                    @if($sol->estado === 'aceptada') ✅ Aceptada
                                                    @elseif($sol->estado === 'rechazada') ❌ Rechazada
                                                    @else ⏳ Pendiente
                                                    @endif
                                                </span>
                                            </div>

                                            @if($sol->observaciones)
                                                <div style="font-size:.75rem; color:#374151; background:white;
                                                            border-radius:6px; padding:6px 8px; margin-bottom:6px;
                                                            border:1px solid #e5e7eb;">
                                                    💬 <em>{{ $sol->observaciones }}</em>
                                                </div>
                                            @endif

                                            @if($sol->estado === 'aceptada' && $sol->cita)
                                                <div style="background:white; border-radius:8px; padding:8px 10px;
                                                            border:1px solid #bbf7d0; font-size:.78rem; color:#166534;">
                                                    <div style="font-weight:700; margin-bottom:3px;">📅 Cita programada</div>
                                                    <div>📆 {{ \Carbon\Carbon::parse($sol->cita->fecha)->format('d M Y') }}
                                                         &nbsp;🕐 {{ $sol->cita->hora }}</div>
                                                    <div style="margin-top:2px;">📍 {{ $sol->cita->lugar }}</div>
                                                    @if($sol->cita->codigo_verificacion)
                                                        <div style="margin-top:5px; background:#dcfce7; border-radius:5px;
                                                                    padding:3px 7px; font-weight:700; letter-spacing:1px;">
                                                            🔑 {{ $sol->cita->codigo_verificacion }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif($sol->estado === 'aceptada')
                                                <div style="font-size:.75rem; color:#6b7280; font-style:italic;">
                                                    ⌛ Tu cita será programada pronto.
                                                </div>
                                            @endif

                                            @if(in_array($sol->estado, ['aceptada', 'rechazada']))
                                            <button
                                                onclick="eliminarActividad('adopcion', {{ $sol->id }}, this)"
                                                style="background:none; border:none; color:#9ca3af;
                                                       font-size:.72rem; padding:0; margin-top:8px;
                                                       cursor:pointer; text-decoration:underline; display:block;">
                                                🗑 Quitar de mi historial
                                            </button>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted" style="font-size:.85rem;">
                                        <p class="mb-1">🐾</p>
                                        <p class="mb-0">Sin solicitudes activas</p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 3: NOTIFICACIONES -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="noti-header-actions">
                                    <h5 class="mb-0">
                                        🔔 Notificaciones
                                        @if($noLeidasCount > 0)
                                            <span class="badge bg-danger rounded-pill ms-1"
                                                  style="font-size:0.72rem;">
                                                {{ $noLeidasCount }}
                                            </span>
                                        @endif
                                    </h5>
                                    @if($noLeidasCount > 0)
                                        <button class="btn-leer-todas" onclick="leerTodas()">
                                            ✓ Leer todas
                                        </button>
                                    @endif
                                </div>
                                <div id="lista-notificaciones" style="max-height:420px; overflow-y:auto;">
                                    @forelse($notificaciones as $noti)
                                    <div class="noti-item tipo-{{ $noti->tipo }} {{ $noti->leida ? '' : 'no-leida' }}"
                                         id="noti-{{ $noti->id }}">
                                        @if(!$noti->leida)
                                            <span class="noti-punto"
                                                  style="display:inline-block; width:8px; height:8px;
                                                         background:#198754; border-radius:50%;
                                                         margin-right:6px; vertical-align:middle;"></span>
                                        @endif
                                        <div class="noti-titulo">{{ $noti->titulo }}</div>
                                        <div class="noti-mensaje">{{ $noti->mensaje }}</div>
                                        <div class="noti-fecha">{{ $noti->created_at->diffForHumans() }}</div>
                                        <div class="noti-acciones">
                                            @if(!$noti->leida)
                                                <button class="btn-leer-noti" onclick="leerNoti({{ $noti->id }}, this)">
                                                    ✓ Marcar leída
                                                </button>
                                            @endif
                                            <button class="btn-del-noti" onclick="eliminarNoti({{ $noti->id }})">
                                                🗑 Eliminar
                                            </button>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="sin-notis" id="sin-notis">
                                        <p>🔔</p>
                                        <small>No tienes notificaciones.</small>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// ── Tabs
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('tab-dinero').classList.add('d-none');
    document.getElementById('tab-especie').classList.add('d-none');
    document.getElementById('tab-' + tab).classList.remove('d-none');
}

// ── Montos rápidos
function elegirMonto(valor) {
    document.getElementById('monto').value = valor;
    document.getElementById('monto').classList.remove('is-invalid', 'is-valid');
    document.getElementById('err_monto').style.display = 'none';
    document.querySelectorAll('.monto-chip').forEach(c => c.classList.remove('seleccionado'));
    event.target.classList.add('seleccionado');
}

// ── Helper marcar campo válido/inválido
function marcar(id, errId, valido) {
    const campo = document.getElementById(id);
    const err   = document.getElementById(errId);
    if (valido) {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        if (err) err.style.display = 'none';
    } else {
        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
        if (err) err.style.display = 'block';
    }
    return valido;
}

// ── Limpiar estado al escribir
['monto','esp_categoria','esp_especie','esp_cantidad','esp_descripcion','esp_direccion','esp_telefono'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input',  () => el.classList.remove('is-invalid', 'is-valid'));
    el.addEventListener('change', () => el.classList.remove('is-invalid', 'is-valid'));
});

// ── Bloquear letras en monto: solo números y un punto decimal
document.getElementById('monto').addEventListener('keydown', function(e) {
    const permitidos = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','.'];
    if (permitidos.includes(e.key)) return;
    if (e.key >= '0' && e.key <= '9') return;
    e.preventDefault();
});
document.getElementById('monto').addEventListener('input', function() {
    let val = this.value.replace(/[^0-9.]/g, '');
    const partes = val.split('.');
    if (partes.length > 2) val = partes[0] + '.' + partes.slice(1).join('');
    if (partes[1] && partes[1].length > 2) val = partes[0] + '.' + partes[1].slice(0, 2);
    this.value = val;
});

// ── Validación DINERO
document.getElementById('formDinero').addEventListener('submit', function(e) {
    const alertaBox = document.getElementById('alerta-dinero');
    const listaErrs = document.getElementById('lista-errores-dinero');
    alertaBox.style.display = 'none';
    listaErrs.innerHTML = '';

    let valido = true;
    const errores = [];

    const montoRaw = document.getElementById('monto').value.trim();
    const montoVal = parseFloat(montoRaw);

    if (montoRaw === '') {
        marcar('monto', 'err_monto', false);
        document.getElementById('err_monto').textContent = '⚠️ El monto es obligatorio.';
        errores.push('Monto (campo obligatorio)');
        valido = false;
    } else if (!/^\d+(\.\d{1,2})?$/.test(montoRaw)) {
        marcar('monto', 'err_monto', false);
        document.getElementById('err_monto').textContent = '⚠️ Solo se permiten números en el monto.';
        errores.push('Monto (solo se permiten números)');
        valido = false;
    } else if (montoVal < 1 || montoVal > 10000) {
        marcar('monto', 'err_monto', false);
        document.getElementById('err_monto').textContent = '⚠️ Ingresa un monto válido (mínimo $1, máximo $10.000).';
        errores.push('Monto (mínimo $1, máximo $10.000 USD)');
        valido = false;
    } else {
        marcar('monto', 'err_monto', true);
    }

    if (!valido) {
        e.preventDefault();
        errores.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            listaErrs.appendChild(li);
        });
        alertaBox.style.display = 'block';
        alertaBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// ── Validación INSUMOS
document.getElementById('formEspecie').addEventListener('submit', function(e) {
    const alertaBox = document.getElementById('alerta-especie');
    const listaErrs = document.getElementById('lista-errores-especie');
    alertaBox.style.display = 'none';
    listaErrs.innerHTML = '';

    let valido = true;
    const errores = [];

    // Categoría
    if (!document.getElementById('esp_categoria').value) {
        marcar('esp_categoria', 'err_esp_categoria', false);
        errores.push('Categoría del insumo');
        valido = false;
    } else {
        marcar('esp_categoria', 'err_esp_categoria', true);
    }

    // Especie destino
    if (!document.getElementById('esp_especie').value) {
        marcar('esp_especie', 'err_esp_especie', false);
        errores.push('¿Para qué mascota es?');
        valido = false;
    } else {
        marcar('esp_especie', 'err_esp_especie', true);
    }

    // Cantidad
    const cantidadVal = parseInt(document.getElementById('esp_cantidad').value, 10);
    if (isNaN(cantidadVal) || cantidadVal < CANTIDAD_MIN || cantidadVal > CANTIDAD_MAX) {
        marcar('esp_cantidad', 'err_esp_cantidad', false);
        errores.push('Cantidad (número entre 1 y 50)');
        valido = false;
    } else {
        marcar('esp_cantidad', 'err_esp_cantidad', true);
    }

    // Descripción
    const descripcionVal = document.getElementById('esp_descripcion').value.trim();
    if (descripcionVal === '') {
        marcar('esp_descripcion', 'err_esp_descripcion', false);
        document.getElementById('err_esp_descripcion').textContent = '⚠️ La descripción es obligatoria.';
        errores.push('Descripción (campo obligatorio)');
        valido = false;
    } else if (descripcionVal.length < 5) {
        marcar('esp_descripcion', 'err_esp_descripcion', false);
        document.getElementById('err_esp_descripcion').textContent = '⚠️ La descripción debe tener al menos 5 caracteres.';
        errores.push('Descripción (mínimo 5 caracteres)');
        valido = false;
    } else {
        marcar('esp_descripcion', 'err_esp_descripcion', true);
    }

    // Dirección
    const direccionVal = document.getElementById('esp_direccion').value.trim();
    if (direccionVal === '') {
        marcar('esp_direccion', 'err_esp_direccion', false);
        document.getElementById('err_esp_direccion').textContent = '⚠️ La dirección de recolección es obligatoria.';
        errores.push('Dirección de recolección (campo obligatorio)');
        valido = false;
    } else if (direccionVal.length < 10) {
        marcar('esp_direccion', 'err_esp_direccion', false);
        document.getElementById('err_esp_direccion').textContent = '⚠️ Escribe una dirección completa (mínimo 10 caracteres).';
        errores.push('Dirección de recolección (mínimo 10 caracteres)');
        valido = false;
    } else {
        marcar('esp_direccion', 'err_esp_direccion', true);
    }

    // Teléfono
    const telefonoVal = document.getElementById('esp_telefono').value.trim();
    if (telefonoVal === '') {
        marcar('esp_telefono', 'err_esp_telefono', false);
        document.getElementById('err_esp_telefono').textContent = '⚠️ El teléfono de contacto es obligatorio.';
        errores.push('Teléfono de contacto (campo obligatorio)');
        valido = false;
    } else if (!/^\d{7,10}$/.test(telefonoVal)) {
        marcar('esp_telefono', 'err_esp_telefono', false);
        document.getElementById('err_esp_telefono').textContent = '⚠️ Ingresa un teléfono válido (7 a 10 dígitos, solo números).';
        errores.push('Teléfono de contacto (7 a 10 dígitos, solo números)');
        valido = false;
    } else {
        marcar('esp_telefono', 'err_esp_telefono', true);
    }

    if (!valido) {
        e.preventDefault();
        errores.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            listaErrs.appendChild(li);
        });
        alertaBox.style.display = 'block';
        alertaBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// ── Stepper de cantidad
const CANTIDAD_MIN = 1;
const CANTIDAD_MAX = 50;

function cambiarCantidad(delta) {
    const input = document.getElementById('esp_cantidad');
    let valor = parseInt(input.value, 10) || 0;
    valor += delta;
    if (valor < CANTIDAD_MIN) valor = CANTIDAD_MIN;
    if (valor > CANTIDAD_MAX) valor = CANTIDAD_MAX;
    input.value = valor;
    input.classList.remove('is-invalid', 'is-valid');
    document.getElementById('err_esp_cantidad').style.display = 'none';
    document.getElementById('btn_menos').disabled = valor <= CANTIDAD_MIN;
    document.getElementById('btn_mas').disabled   = valor >= CANTIDAD_MAX;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn_menos').disabled = true;
});

// ══ NOTIFICACIONES ══

function leerNoti(id, btn) {
    fetch('/notificaciones/' + id + '/leer', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        const item = document.getElementById('noti-' + id);
        item.classList.remove('no-leida');
        item.querySelector('.noti-punto')?.remove();
        btn.remove();
        actualizarBadge(-1);
    });
}

function eliminarNoti(id) {
    const item = document.getElementById('noti-' + id);
    const eraNoLeida = item.classList.contains('no-leida');
    fetch('/notificaciones/' + id + '/eliminar', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        item.style.transition = 'opacity 0.3s, transform 0.3s';
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';
        setTimeout(() => {
            item.remove();
            if (eraNoLeida) actualizarBadge(-1);
            const lista = document.getElementById('lista-notificaciones');
            if (!lista.querySelector('.noti-item')) {
                lista.innerHTML = '<div class="sin-notis"><p>🔔</p><small>No tienes notificaciones.</small></div>';
            }
        }, 300);
    });
}

function leerTodas() {
    fetch('/notificaciones/leer-todas', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        document.querySelectorAll('.noti-item.no-leida').forEach(item => {
            item.classList.remove('no-leida');
            item.querySelector('.noti-punto')?.remove();
            item.querySelector('.btn-leer-noti')?.remove();
        });
        document.querySelectorAll('.badge-noti, .badge.bg-danger').forEach(b => b.remove());
        document.querySelector('.btn-leer-todas')?.remove();
    });
}

function actualizarBadge(delta) {
    const badge = document.querySelector('.badge-noti');
    if (!badge) return;
    let count = parseInt(badge.textContent) + delta;
    if (count <= 0) {
        badge.remove();
        document.querySelector('.badge.bg-danger.rounded-pill')?.remove();
        document.querySelector('.btn-leer-todas')?.remove();
    } else {
        badge.textContent = count;
    }
}

// ══ ACTIVIDADES ══

function eliminarActividad(tipo, id, btn) {
    const rutas = {
        'donacion_dinero':  '/actividad/donacion-dinero/'  + id + '/ocultar',
        'donacion_especie': '/actividad/donacion-especie/' + id + '/ocultar',
        'adopcion':         '/actividad/adopcion/'         + id + '/ocultar',
    };
    const item = document.getElementById('actividad-' + tipo + '-' + id);
    fetch(rutas[tipo], {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        item.style.transition = 'opacity 0.3s, transform 0.3s';
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';
        setTimeout(() => item.remove(), 300);
    });
}
</script>
</body>
</html>