<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas - Admin Adoptafest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/admin_citas.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="tiene-sidebar">
@if(session('rol') == 'admin')
<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-brand">
        <img src="{{ asset('storage/img/logo de empresa.png') }}" alt="Logo" class="sidebar-logo">
        <span class="sidebar-brand-text">Adoptafest</span>
    </div>

    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Contraer menú" title="Contraer menú">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>

    <div class="sidebar-user">
        <div class="sidebar-avatar">{{ strtoupper(substr(session('nombre', 'A'), 0, 1)) }}</div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ session('nombre') }}</span>
            <span class="sidebar-user-rol">Administrador</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="sidebar-section-label">Gestión</span>
        <a href="/admin" class="sidebar-link {{ request()->is('admin') ? 'activo' : '' }}" data-label="Usuarios">
            <span class="sidebar-icon">👥</span><span class="sidebar-text">Usuarios</span>
        </a>
        <a href="/admin_mascotas" class="sidebar-link {{ request()->is('admin_mascotas') ? 'activo' : '' }}" data-label="Mascotas">
            <span class="sidebar-icon">🐾</span><span class="sidebar-text">Mascotas</span>
        </a>
        <a href="/admin_eventos" class="sidebar-link {{ request()->is('admin_eventos') ? 'activo' : '' }}" data-label="Eventos">
            <span class="sidebar-icon">🎉</span><span class="sidebar-text">Eventos</span>
        </a>
        <a href="/admin_inscripciones" class="sidebar-link {{ request()->is('admin_inscripciones') ? 'activo' : '' }}" data-label="Inscripciones">
            <span class="sidebar-icon">📋</span><span class="sidebar-text">Inscripciones</span>
        </a>
        <a href="/admin_adopciones" class="sidebar-link {{ request()->is('admin_adopciones') ? 'activo' : '' }}" data-label="Adopciones">
            <span class="sidebar-icon">💚</span><span class="sidebar-text">Adopciones</span>
        </a>
        <a href="/admin_citas" class="sidebar-link {{ request()->is('admin_citas') ? 'activo' : '' }}" data-label="Citas">
            <span class="sidebar-icon">📅</span><span class="sidebar-text">Citas</span>
        </a>
        <a href="/admin_donaciones" class="sidebar-link {{ request()->is('admin_donaciones') ? 'activo' : '' }}" data-label="Donaciones">
            <span class="sidebar-icon">💸</span><span class="sidebar-text">Donaciones</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/inicio" class="sidebar-link sidebar-link-secundario" data-label="Ver sitio">
            <span class="sidebar-icon">🏠</span><span class="sidebar-text">Ver sitio</span>
        </a>
        <a href="/logout" class="sidebar-link sidebar-link-logout" data-label="Cerrar sesión">
            <span class="sidebar-icon">🚪</span><span class="sidebar-text">Cerrar sesión</span>
        </a>
    </div>
</aside>

<button class="sidebar-mobile-toggle" id="sidebarMobileToggle" aria-label="Abrir menú">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
@else
<div class="login-flotante">
    <a href="/login" class="btn btn-warning">Login</a>
</div>
@endif

<!-- HERO -->
<div class="hero">
    <div class="container">
        <h1>📅 Gestión de Citas</h1>
        <p>Agenda y controla citas de adopción y recolección de donaciones</p>
    </div>
</div>

<div class="contenido">
    <div class="container" style="max-width: 960px;">

        @if(session('exito'))
            <div class="alert alert-success rounded-3 mb-4">✅ {{ session('exito') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 mb-4">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                <strong>⚠️ No se pudo guardar:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ══ MINI-PESTAÑAS ══ -->
        <div class="mini-tabs-wrapper">
            <button class="mini-tab-btn activo" onclick="cambiarSeccion('adopcion', this)">
                🐾 Citas de Adopción
            </button>
            <button class="mini-tab-btn" onclick="cambiarSeccion('donacion', this)">
                📦 Recolección de Donaciones
            </button>
        </div>

        <!-- ══ SECCIÓN ADOPCIÓN ══ -->
        <div id="seccion-adopcion">

            <!-- STATS -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-numero">{{ $citasAdopcion->count() }}</div>
                        <div class="stat-label">Total citas</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#16a34a;">
                            {{ $citasAdopcion->where('estado','programada')->count() }}
                        </div>
                        <div class="stat-label">Programadas</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#2563eb;">
                            {{ $citasAdopcion->where('estado','completada')->count() }}
                        </div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#f59e0b;">
                            {{ $solicitudesAceptadas->count() }}
                        </div>
                        <div class="stat-label">Sin cita aún</div>
                    </div>
                </div>
            </div>

            <div class="tabs-wrapper">
                <button class="tab-btn activo" onclick="cambiarTab('agendar', this)">
                    ➕ Agendar nueva cita
                </button>
                <button class="tab-btn" onclick="cambiarTab('citas', this)">
                    📋 Ver todas las citas
                </button>
            </div>

            {{-- ── TAB: AGENDAR ADOPCIÓN ── --}}
            <div id="tab-agendar">
                @if($solicitudesAceptadas->count() > 0)
                <div class="panel-agendar">
                    <div class="panel-agendar-header">
                        <h4>➕ Agendar cita de adopción</h4>
                        <small style="opacity:.85;">Selecciona una solicitud aceptada y completa los datos de la cita</small>
                    </div>
                    <div class="panel-agendar-body">
                        <p class="fw-semibold small text-muted mb-3">
                            📋 Solicitudes aceptadas pendientes de cita ({{ $solicitudesAceptadas->count() }}):
                        </p>

                        @foreach($solicitudesAceptadas as $s)
                        <div class="solicitud-select" id="sel-{{ $s->id }}" onclick="seleccionar({{ $s->id }})">
                            <img src="{{ asset('storage/img/' . ($s->mascota->imagen ?? 'placeholder.png')) }}"
                                 onerror="this.src='{{ asset('storage/img/placeholder.png') }}'">
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="color:#14532d;">🐾 {{ $s->mascota->nombre ?? '?' }}</div>
                                <small class="text-muted">
                                    👤 {{ $s->nombre_completo }} &nbsp;|&nbsp;
                                    📞 {{ $s->telefono }} &nbsp;|&nbsp;
                                    📅 Aceptada: {{ $s->updated_at->format('d/m/Y') }}
                                </small>
                            </div>
                            <span style="font-size:1.4rem;">👆</span>
                        </div>

                        <div class="form-cita" id="form-{{ $s->id }}">

                            {{-- ALERTA ERRORES ADOPCIÓN --}}
                            <div id="alerta-adopcion-{{ $s->id }}" class="alert alert-danger rounded-3 mb-3" style="display:none;">
                                <strong>⚠️ No se pudo guardar:</strong>
                                <ul id="lista-errores-adopcion-{{ $s->id }}" class="mb-0 mt-2"></ul>
                            </div>

                            <form action="/admin_citas/{{ $s->id }}/agendar" method="POST"
                                  id="formAdopcion-{{ $s->id }}" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">📅 Fecha de la cita <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha"
                                               id="fecha-adopcion-{{ $s->id }}"
                                               class="form-control" required
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                        <div class="invalid-feedback">⚠️ Selecciona una fecha válida (a partir de mañana).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">⏰ Hora <span class="text-danger">*</span></label>
                                        <input type="time" name="hora"
                                               id="hora-adopcion-{{ $s->id }}"
                                               class="form-control" required
                                               min="08:00" max="22:00">
                                        <div class="invalid-feedback">⚠️ La hora debe estar entre 8:00 AM y 10:00 PM.</div>
                                        <small class="form-text text-muted">Horario permitido: 8:00 AM – 10:00 PM.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">📍 Lugar <span class="text-danger">*</span></label>
                                        <input type="text" name="lugar"
                                               id="lugar-adopcion-{{ $s->id }}"
                                               class="form-control" required
                                               placeholder="Ej: Refugio Adoptafest — Sede Principal">
                                        <div class="invalid-feedback">⚠️ El lugar es obligatorio (mínimo 5 caracteres).</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">🗺️ Dirección exacta</label>
                                        <input type="text" name="direccion_cita"
                                               id="dir-adopcion-{{ $s->id }}"
                                               class="form-control"
                                               placeholder="Ej: Cra 15 #80-45, Bogotá">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">📝 Notas para el adoptante</label>
                                        <textarea name="notas" class="form-control" rows="2"
                                                  placeholder="Ej: Traer documento de identidad y el código de verificación..."></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 rounded-3" style="background:#1e3a5f; color:white; font-size:.85rem;">
                                    🔐 <strong>Código de verificación:</strong>
                                    Se generará automáticamente al agendar y se enviará al usuario por notificación.
                                </div>
                                <button type="submit" class="btn-agendar mt-3"
                                        onclick="validarAdopcion(event, {{ $s->id }})">
                                    📅 Confirmar y agendar cita
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="empty-state">
                    <p style="font-size:4rem;">📅</p>
                    <h5 class="fw-bold">Sin solicitudes pendientes de cita</h5>
                    <p>Todas las solicitudes aceptadas ya tienen cita agendada.</p>
                    <a href="/admin_adopciones" class="btn btn-success rounded-pill px-4">Ver solicitudes</a>
                </div>
                @endif
            </div>

            {{-- ── TAB: VER CITAS DE ADOPCIÓN ── --}}
            <div id="tab-citas" style="display:none;">
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <button class="tab-btn activo" onclick="filtrarCitas('todas', this)">Todas</button>
                    <button class="tab-btn" onclick="filtrarCitas('programada', this)">🟢 Programadas</button>
                    <button class="tab-btn" onclick="filtrarCitas('completada', this)">🔵 Completadas</button>
                    <button class="tab-btn" onclick="filtrarCitas('cancelada', this)">🔴 Canceladas</button>
                </div>

                @if($citasAdopcion->count() > 0)
                <div class="timeline-wrapper">
                    <div class="timeline-line"></div>
                    @foreach($citasAdopcion as $cita)
                    <div class="cita-card cita-item" data-estado="{{ $cita->estado }}">
                        <div class="timeline-dot dot-{{ $cita->estado }}">
                            @if($cita->estado == 'programada') 📅
                            @elseif($cita->estado == 'completada') ✅
                            @else ❌ @endif
                        </div>

                        <div class="cita-header">
                            <div class="cita-fecha-box">
                                <div class="dia">{{ \Carbon\Carbon::parse($cita->fecha)->format('d') }}</div>
                                <div class="mes">{{ strtoupper(\Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('MMM YYYY')) }}</div>
                                <div class="hora">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                            </div>
                            <div class="cita-info">
                                <div class="titulo">🐾 {{ $cita->mascota->nombre ?? 'Mascota' }}</div>
                                <div class="sub">
                                    👤 {{ $cita->solicitud->nombre_completo ?? $cita->user->name ?? '—' }}
                                    &nbsp;|&nbsp; 📞 {{ $cita->solicitud->telefono ?? '—' }}
                                </div>
                                <div class="sub">📍 {{ $cita->lugar }}</div>
                            </div>
                            <span class="badge-estado-cita estado-{{ $cita->estado }}">
                                @if($cita->estado == 'programada') 🟢 Programada
                                @elseif($cita->estado == 'completada') 🔵 Completada
                                @else 🔴 Cancelada @endif
                            </span>
                        </div>

                        <div class="codigo-box">
                            <div class="codigo-icono">🔐</div>
                            <div>
                                <div class="codigo-label">Código de verificación del adoptante</div>
                                <div class="codigo-valor">{{ $cita->codigo_verificacion }}</div>
                            </div>
                            <div class="codigo-estado">
                                @if($cita->verificada)
                                    <span class="badge-verificada">✓ Verificado</span>
                                @else
                                    <span class="badge-pendiente-v">⏳ Sin verificar</span>
                                @endif
                            </div>
                        </div>

                        <div class="cita-detalles">
                            <div class="detalle-item"><strong>📅 Fecha</strong><span>{{ \Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('dddd D [de] MMMM') }}</span></div>
                            <div class="detalle-item"><strong>⏰ Hora</strong><span>{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }} hrs</span></div>
                            <div class="detalle-item"><strong>📍 Lugar</strong><span>{{ $cita->lugar }}</span></div>
                            @if($cita->direccion_cita)
                            <div class="detalle-item"><strong>🗺️ Dirección</strong><span>{{ $cita->direccion_cita }}</span></div>
                            @endif
                            <div class="detalle-item"><strong>📧 Correo</strong><span>{{ $cita->user->email ?? '—' }}</span></div>
                            <div class="detalle-item"><strong>🆔 Cédula</strong><span>{{ $cita->solicitud->cedula ?? '—' }}</span></div>
                        </div>

                        @if($cita->notas)
                        <div class="notas-box">📝 <strong>Notas:</strong> {{ $cita->notas }}</div>
                        @endif

                        @if($cita->estado == 'programada')
                        <div class="cita-acciones">
                            <form action="/admin_citas/{{ $cita->id }}/estado" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" name="estado" value="completada" class="btn-completar">✅ Marcar completada</button>
                            </form>
                            <form action="/admin_citas/{{ $cita->id }}/estado" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" name="estado" value="cancelada" class="btn-cancelar-cita">❌ Cancelar cita</button>
                            </form>
                            <small class="text-muted ms-auto">Agendada: {{ $cita->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @else
                        <div class="cita-acciones">
                            <small class="text-muted">Última actualización: {{ $cita->updated_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @endif

                        <button type="button"
                                class="btn btn-sm btn-danger w-100 mt-3"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminarCita{{ $cita->id }}">
                            🗑️ Eliminar cita
                        </button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <p style="font-size:4rem;">📅</p>
                    <h5 class="fw-bold">Sin citas de adopción registradas</h5>
                    <p>Agenda la primera cita desde la pestaña anterior.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- ══ SECCIÓN DONACIÓN ══ -->
        <div id="seccion-donacion" style="display:none;">

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#f59e0b;">{{ $donacionesAprobadas->count() }}</div>
                        <div class="stat-label">Esperando recolección</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#16a34a;">{{ $citasDonacion->where('estado','programada')->count() }}</div>
                        <div class="stat-label">Recolecciones programadas</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card">
                        <div class="stat-numero" style="color:#2563eb;">{{ $citasDonacion->where('estado','completada')->count() }}</div>
                        <div class="stat-label">Recolectadas</div>
                    </div>
                </div>
            </div>

            <div class="tabs-wrapper">
                <button class="tab-btn activo" onclick="cambiarTabDonacion('agendar', this)">
                    ➕ Agendar recolección
                </button>
                <button class="tab-btn" onclick="cambiarTabDonacion('citas', this)">
                    📋 Ver recolecciones
                </button>
            </div>

            {{-- ── AGENDAR RECOLECCIÓN ── --}}
            <div id="tab-agendar-donacion">
                @if($donacionesAprobadas->count() > 0)
                <div class="panel-agendar">
                    <div class="panel-agendar-header">
                        <h4>📦 Agendar recolección de donación</h4>
                        <small style="opacity:.85;">Selecciona una donación aprobada y define cuándo pasará el repartidor</small>
                    </div>
                    <div class="panel-agendar-body">
                        <p class="fw-semibold small text-muted mb-3">
                            📦 Donaciones aprobadas pendientes de recolección ({{ $donacionesAprobadas->count() }}):
                        </p>

                        @foreach($donacionesAprobadas as $d)
                        @php $insignia = $d->user ? $d->user->insigniaDonante() : null; @endphp
                        <div class="solicitud-select" id="sel-don-{{ $d->id }}" onclick="seleccionarDonacion({{ $d->id }})">
                            <div class="icono-categoria">📦</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="color:#14532d;">
                                    {{ ucfirst(str_replace('_',' ', $d->categoria)) }} — {{ $d->cantidad }} unid.
                                    @if($insignia)
                                        <span class="badge-donante" style="background:{{ $insignia['color'] }}1a;color:{{ $insignia['color'] }};border:1px solid {{ $insignia['color'] }}40;">
                                            {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    👤 {{ $d->user->name ?? '—' }} &nbsp;|&nbsp;
                                    📞 {{ $d->telefono_contacto }} &nbsp;|&nbsp;
                                    🐾 {{ ucfirst(str_replace('_',' ', $d->especie_destino)) }}
                                </small>
                                <div class="small text-muted mt-1">📍 {{ $d->direccion_recoleccion }}</div>
                            </div>
                            <span style="font-size:1.4rem;">👆</span>
                        </div>

                        <div class="form-cita" id="form-don-{{ $d->id }}">

                            {{-- ALERTA ERRORES DONACIÓN --}}
                            <div id="alerta-donacion-{{ $d->id }}" class="alert alert-danger rounded-3 mb-3" style="display:none;">
                                <strong>⚠️ No se pudo guardar:</strong>
                                <ul id="lista-errores-donacion-{{ $d->id }}" class="mb-0 mt-2"></ul>
                            </div>

                            <form action="{{ route('admin_citas.donacion.agendar', $d->id) }}" method="POST"
                                  id="formDonacion-{{ $d->id }}" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">📅 Fecha de recolección <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha"
                                               id="fecha-donacion-{{ $d->id }}"
                                               class="form-control" required
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                        <div class="invalid-feedback">⚠️ Selecciona una fecha válida (a partir de mañana).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">⏰ Hora <span class="text-danger">*</span></label>
                                        <input type="time" name="hora"
                                               id="hora-donacion-{{ $d->id }}"
                                               class="form-control" required
                                               min="08:00" max="22:00">
                                        <div class="invalid-feedback">⚠️ La hora debe estar entre 8:00 AM y 10:00 PM.</div>
                                        <small class="form-text text-muted">Horario permitido: 8:00 AM – 10:00 PM.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">📝 Notas para el repartidor / donante</label>
                                        <textarea name="notas" class="form-control" rows="2"
                                                  placeholder="Ej: Preguntar en portería por la donación de Adoptafest..."></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 rounded-3" style="background:#1e3a5f; color:white; font-size:.85rem;">
                                    🚚 Esta donación se recogerá en: <strong>{{ $d->direccion_recoleccion }}</strong>
                                </div>
                                <button type="submit" class="btn-agendar mt-3"
                                        onclick="validarDonacion(event, {{ $d->id }})">
                                    📅 Confirmar y agendar recolección
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="empty-state">
                    <p style="font-size:4rem;">📦</p>
                    <h5 class="fw-bold">Sin donaciones pendientes de recolección</h5>
                    <p>Aprueba donaciones en especie desde Admin Donaciones para que aparezcan aquí.</p>
                    <a href="/admin_donaciones" class="btn btn-success rounded-pill px-4">Ver donaciones</a>
                </div>
                @endif
            </div>

            {{-- ── VER RECOLECCIONES ── --}}
            <div id="tab-citas-donacion" style="display:none;">
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <button class="tab-btn activo" onclick="filtrarCitasDonacion('todas', this)">Todas</button>
                    <button class="tab-btn" onclick="filtrarCitasDonacion('programada', this)">🟢 Programadas</button>
                    <button class="tab-btn" onclick="filtrarCitasDonacion('completada', this)">🔵 Completadas</button>
                    <button class="tab-btn" onclick="filtrarCitasDonacion('cancelada', this)">🔴 Canceladas</button>
                </div>

                @if($citasDonacion->count() > 0)
                <div class="timeline-wrapper">
                    <div class="timeline-line"></div>
                    @foreach($citasDonacion as $cita)
                    <div class="cita-card cita-item-donacion" data-estado="{{ $cita->estado }}">
                        <div class="timeline-dot dot-{{ $cita->estado }}">
                            @if($cita->estado == 'programada') 🚚
                            @elseif($cita->estado == 'completada') ✅
                            @else ❌ @endif
                        </div>

                        <div class="cita-header">
                            <div class="cita-fecha-box">
                                <div class="dia">{{ \Carbon\Carbon::parse($cita->fecha)->format('d') }}</div>
                                <div class="mes">{{ strtoupper(\Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('MMM YYYY')) }}</div>
                                <div class="hora">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                            </div>
                            <div class="cita-info">
                                <div class="titulo">
                                    📦 {{ ucfirst(str_replace('_',' ', $cita->donacionEspecie->categoria ?? '—')) }}
                                </div>
                                <div class="sub">
                                    👤 {{ $cita->user->name ?? '—' }} &nbsp;|&nbsp;
                                    📞 {{ $cita->donacionEspecie->telefono_contacto ?? '—' }}
                                </div>
                                <div class="sub">📍 {{ $cita->direccion_recoleccion }}</div>
                            </div>
                            <span class="badge-estado-cita estado-{{ $cita->estado }}">
                                @if($cita->estado == 'programada') 🟢 Programada
                                @elseif($cita->estado == 'completada') 🔵 Completada
                                @else 🔴 Cancelada @endif
                            </span>
                        </div>

                        @if($cita->notas)
                        <div class="notas-box">📝 <strong>Notas:</strong> {{ $cita->notas }}</div>
                        @endif

                        @if($cita->estado == 'programada')
                        <div class="cita-acciones">
                            <form action="{{ route('admin_citas.donacion.estado', $cita->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" name="estado" value="completada" class="btn-completar">
                                    ✅ Marcar recolectada
                                </button>
                            </form>
                            <form action="{{ route('admin_citas.donacion.estado', $cita->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" name="estado" value="cancelada" class="btn-cancelar-cita">❌ Cancelar</button>
                            </form>
                            <small class="text-muted ms-auto">Agendada: {{ $cita->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @else
                        <div class="cita-acciones">
                            <small class="text-muted">Última actualización: {{ $cita->updated_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @endif

                        <button type="button"
                                class="btn btn-sm btn-danger w-100 mt-3"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminarRecoleccion{{ $cita->id }}">
                            🗑️ Eliminar recolección
                        </button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <p style="font-size:4rem;">📦</p>
                    <h5 class="fw-bold">Sin recolecciones registradas</h5>
                    <p>Agenda la primera recolección desde la pestaña anterior.</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Modales eliminar citas de adopción --}}
@foreach($citasAdopcion as $cita)
<div class="modal fade" id="modalEliminarCita{{ $cita->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div style="font-size:3.5rem; line-height:1;">🗑️</div>
                <h5 class="fw-bold mt-3 mb-1">¿Eliminar cita de adopción?</h5>
                <p class="text-muted small mb-4">
                    Se eliminará la cita de
                    <strong>{{ $cita->solicitud->nombre_completo ?? $cita->user->name ?? 'este usuario' }}</strong>
                    para <strong>{{ $cita->mascota->nombre ?? 'esta mascota' }}</strong>
                    del <strong>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</strong>.<br>
                    <span class="text-danger fw-semibold">Esta acción no se puede deshacer.</span>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="/admin_citas/{{ $cita->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 rounded-pill">
                            Sí, eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Modales eliminar recolecciones de donación --}}
@foreach($citasDonacion as $cita)
<div class="modal fade" id="modalEliminarRecoleccion{{ $cita->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div style="font-size:3.5rem; line-height:1;">🗑️</div>
                <h5 class="fw-bold mt-3 mb-1">¿Eliminar recolección?</h5>
                <p class="text-muted small mb-4">
                    Se eliminará la recolección de
                    <strong>{{ $cita->user->name ?? 'este usuario' }}</strong>
                    — <strong>{{ ucfirst(str_replace('_',' ', $cita->donacionEspecie->categoria ?? '—')) }}</strong>
                    del <strong>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</strong>.<br>
                    <span class="text-danger fw-semibold">Esta acción no se puede deshacer.</span>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="/admin_citas/donacion/{{ $cita->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 rounded-pill">
                            Sí, eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ══ HORA LÍMITES ══
const HORA_MIN = '08:00';
const HORA_MAX = '22:00';

// ── Helper marcar campo válido/inválido
function marcarCampo(campo, valido) {
    if (valido) {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
    } else {
        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
    }
}

// ── Limpiar estado al interactuar
function limpiarCampo(campo) {
    campo.addEventListener('input',  () => campo.classList.remove('is-invalid', 'is-valid'));
    campo.addEventListener('change', () => campo.classList.remove('is-invalid', 'is-valid'));
}

// ── Validar hora en rango 08:00 – 22:00
function horaValida(valor) {
    if (!valor) return false;
    const [h, m] = valor.split(':').map(Number);
    const minutos = h * 60 + m;
    return minutos >= 8 * 60 && minutos <= 22 * 60;
}

// ── Validar fecha: debe ser posterior a hoy
function fechaValida(valor) {
    if (!valor) return false;
    const hoy  = new Date();
    hoy.setHours(0, 0, 0, 0);
    const sel  = new Date(valor + 'T00:00:00');
    return sel > hoy;
}

// ══ VALIDACIÓN CITA ADOPCIÓN ══
function validarAdopcion(e, id) {
    e.preventDefault();

    const alertaBox = document.getElementById('alerta-adopcion-' + id);
    const listaErrs = document.getElementById('lista-errores-adopcion-' + id);
    alertaBox.style.display = 'none';
    listaErrs.innerHTML = '';

    const campoFecha  = document.getElementById('fecha-adopcion-' + id);
    const campoHora   = document.getElementById('hora-adopcion-'  + id);
    const campoLugar  = document.getElementById('lugar-adopcion-' + id);

    let valido  = true;
    const errores = [];

    // Fecha
    if (!fechaValida(campoFecha.value)) {
        marcarCampo(campoFecha, false);
        errores.push('Fecha de la cita (debe ser a partir de mañana)');
        valido = false;
    } else {
        marcarCampo(campoFecha, true);
    }

    // Hora
    if (!horaValida(campoHora.value)) {
        marcarCampo(campoHora, false);
        errores.push('Hora (debe estar entre 8:00 AM y 10:00 PM)');
        valido = false;
    } else {
        marcarCampo(campoHora, true);
    }

    // Lugar
    if (!campoLugar.value.trim() || campoLugar.value.trim().length < 5) {
        marcarCampo(campoLugar, false);
        errores.push('Lugar de la cita (mínimo 5 caracteres)');
        valido = false;
    } else {
        marcarCampo(campoLugar, true);
    }

    if (!valido) {
        errores.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            listaErrs.appendChild(li);
        });
        alertaBox.style.display = 'block';
        alertaBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    // Sin errores: enviar el form
    document.getElementById('formAdopcion-' + id).submit();
}

// ══ VALIDACIÓN RECOLECCIÓN DONACIÓN ══
function validarDonacion(e, id) {
    e.preventDefault();

    const alertaBox = document.getElementById('alerta-donacion-' + id);
    const listaErrs = document.getElementById('lista-errores-donacion-' + id);
    alertaBox.style.display = 'none';
    listaErrs.innerHTML = '';

    const campoFecha = document.getElementById('fecha-donacion-' + id);
    const campoHora  = document.getElementById('hora-donacion-'  + id);

    let valido  = true;
    const errores = [];

    // Fecha
    if (!fechaValida(campoFecha.value)) {
        marcarCampo(campoFecha, false);
        errores.push('Fecha de recolección (debe ser a partir de mañana)');
        valido = false;
    } else {
        marcarCampo(campoFecha, true);
    }

    // Hora
    if (!horaValida(campoHora.value)) {
        marcarCampo(campoHora, false);
        errores.push('Hora (debe estar entre 8:00 AM y 10:00 PM)');
        valido = false;
    } else {
        marcarCampo(campoHora, true);
    }

    if (!valido) {
        errores.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            listaErrs.appendChild(li);
        });
        alertaBox.style.display = 'block';
        alertaBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    document.getElementById('formDonacion-' + id).submit();
}

// ══ TABS Y NAVEGACIÓN ══
function cambiarSeccion(seccion, btn) {
    document.querySelectorAll('.mini-tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('seccion-adopcion').style.display = seccion === 'adopcion' ? 'block' : 'none';
    document.getElementById('seccion-donacion').style.display = seccion === 'donacion' ? 'block' : 'none';
}

function cambiarTab(tab, btn) {
    btn.parentElement.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('tab-agendar').style.display = tab === 'agendar' ? 'block' : 'none';
    document.getElementById('tab-citas').style.display   = tab === 'citas'   ? 'block' : 'none';
}

let seleccionada = null;
function seleccionar(id) {
    if (seleccionada !== null) {
        document.getElementById('sel-'  + seleccionada).classList.remove('activa');
        document.getElementById('form-' + seleccionada).classList.remove('visible');
    }
    if (seleccionada === id) { seleccionada = null; return; }
    seleccionada = id;
    document.getElementById('sel-'  + id).classList.add('activa');
    document.getElementById('form-' + id).classList.add('visible');
    document.getElementById('form-' + id).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function filtrarCitas(estado, btn) {
    btn.parentElement.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('.cita-item').forEach(el => {
        el.style.display = (estado === 'todas' || el.dataset.estado === estado) ? '' : 'none';
    });
}

function cambiarTabDonacion(tab, btn) {
    btn.parentElement.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('tab-agendar-donacion').style.display = tab === 'agendar' ? 'block' : 'none';
    document.getElementById('tab-citas-donacion').style.display   = tab === 'citas'   ? 'block' : 'none';
}

let seleccionadaDon = null;
function seleccionarDonacion(id) {
    if (seleccionadaDon !== null) {
        document.getElementById('sel-don-'  + seleccionadaDon).classList.remove('activa');
        document.getElementById('form-don-' + seleccionadaDon).classList.remove('visible');
    }
    if (seleccionadaDon === id) { seleccionadaDon = null; return; }
    seleccionadaDon = id;
    document.getElementById('sel-don-'  + id).classList.add('activa');
    document.getElementById('form-don-' + id).classList.add('visible');
    document.getElementById('form-don-' + id).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function filtrarCitasDonacion(estado, btn) {
    btn.parentElement.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('.cita-item-donacion').forEach(el => {
        el.style.display = (estado === 'todas' || el.dataset.estado === estado) ? '' : 'none';
    });
}

// ══ SIDEBAR ══
(function () {
    const sidebar = document.getElementById('adminSidebar');
    if (!sidebar) return;

    const toggleBtn    = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('sidebarMobileToggle');
    const overlay      = document.getElementById('sidebarOverlay');
    const body         = document.body;

    const guardado = localStorage.getItem('sidebarColapsado');
    if (guardado === '1') {
        sidebar.classList.add('colapsado');
        body.classList.add('sidebar-colapsado');
    }

    toggleBtn.addEventListener('click', function () {
        const colapsado = sidebar.classList.toggle('colapsado');
        body.classList.toggle('sidebar-colapsado', colapsado);
        localStorage.setItem('sidebarColapsado', colapsado ? '1' : '0');
    });

    function abrirMobile() { sidebar.classList.add('abierto-mobile'); overlay.classList.add('visible'); }
    function cerrarMobile() { sidebar.classList.remove('abierto-mobile'); overlay.classList.remove('visible'); }
    mobileToggle.addEventListener('click', abrirMobile);
    overlay.addEventListener('click', cerrarMobile);
    sidebar.querySelectorAll('.sidebar-link').forEach(link => link.addEventListener('click', cerrarMobile));
})();
</script>
</body>
</html>