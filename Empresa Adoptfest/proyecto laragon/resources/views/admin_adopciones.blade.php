<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Adopciones - Adoptafest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/admin_adopciones.css') }}">
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
            <span class="sidebar-icon">👥</span>
            <span class="sidebar-text">Usuarios</span>
        </a>

        <a href="/admin_mascotas" class="sidebar-link {{ request()->is('admin_mascotas') ? 'activo' : '' }}" data-label="Mascotas">
            <span class="sidebar-icon">🐾</span>
            <span class="sidebar-text">Mascotas</span>
        </a>

        <a href="/admin_eventos" class="sidebar-link {{ request()->is('admin_eventos') ? 'activo' : '' }}" data-label="Eventos">
            <span class="sidebar-icon">🎉</span>
            <span class="sidebar-text">Eventos</span>
        </a>

        <a href="/admin_inscripciones" class="sidebar-link {{ request()->is('admin_inscripciones') ? 'activo' : '' }}" data-label="Inscripciones">
            <span class="sidebar-icon">📋</span>
            <span class="sidebar-text">Inscripciones</span>
        </a>

        <a href="/admin_adopciones" class="sidebar-link {{ request()->is('admin_adopciones') ? 'activo' : '' }}" data-label="Adopciones">
            <span class="sidebar-icon">💚</span>
            <span class="sidebar-text">Adopciones</span>
        </a>

        <a href="/admin_citas" class="sidebar-link {{ request()->is('admin_citas') ? 'activo' : '' }}" data-label="Citas">
            <span class="sidebar-icon">📅</span>
            <span class="sidebar-text">Citas</span>
        </a>

        <a href="/admin_donaciones" class="sidebar-link {{ request()->is('admin_donaciones') ? 'activo' : '' }}" data-label="Donaciones">
            <span class="sidebar-icon">💸</span>
            <span class="sidebar-text">Donaciones</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/inicio" class="sidebar-link sidebar-link-secundario" data-label="Ver sitio">
            <span class="sidebar-icon">🏠</span>
            <span class="sidebar-text">Ver sitio</span>
        </a>
        <a href="/logout" class="sidebar-link sidebar-link-logout" data-label="Cerrar sesión">
            <span class="sidebar-icon">🚪</span>
            <span class="sidebar-text">Cerrar sesión</span>
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
        <h1>💚 Solicitudes de Adopción</h1>
        <p>Revisa, analiza y responde las solicitudes de los adoptantes</p>
    </div>
</div>

<div class="contenido">
    <div class="container" style="max-width: 920px;">

        @if(session('exito'))
            <div class="alert alert-success rounded-3 mb-4">✅ {{ session('exito') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 mb-4">❌ {{ session('error') }}</div>
        @endif

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero">{{ $solicitudes->count() }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#f59e0b;">
                        {{ $solicitudes->where('estado','pendiente')->count() }}
                    </div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#16a34a;">
                        {{ $solicitudes->where('estado','aceptada')->count() }}
                    </div>
                    <div class="stat-label">Aceptadas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#dc2626;">
                        {{ $solicitudes->where('estado','rechazada')->count() }}
                    </div>
                    <div class="stat-label">Rechazadas</div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filtros-wrapper">
            <span style="font-size:.85rem; font-weight:600; color:#6b7280;">Filtrar:</span>
            <button class="filtro-btn activo" onclick="filtrar('todos', this)">Todos</button>
            <button class="filtro-btn" onclick="filtrar('pendiente', this)">⏳ Pendientes</button>
            <button class="filtro-btn" onclick="filtrar('aceptada',  this)">✅ Aceptadas</button>
            <button class="filtro-btn" onclick="filtrar('rechazada', this)">❌ Rechazadas</button>
        </div>

        <!-- LISTA SOLICITUDES -->
        @forelse($solicitudes as $s)
        @php
            $insignia = $s->user ? $s->user->insigniaDonante() : null;
        @endphp
        <div class="card-solicitud solicitud-item" data-estado="{{ $s->estado }}">
            <div class="barra-estado barra-{{ $s->estado }}"></div>
            <div class="p-4">

                <!-- CABECERA -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-user">
                        {{ strtoupper(substr($s->nombre_completo, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-6 d-flex align-items-center gap-2 flex-wrap">
                            {{ $s->nombre_completo }}
                            @if($insignia)
                                <span class="badge-donante"
                                      style="background: {{ $insignia['color'] }}1a; color: {{ $insignia['color'] }}; border: 1px solid {{ $insignia['color'] }}40;"
                                      title="{{ $insignia['etiqueta'] }}">
                                    {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                </span>
                            @endif
                        </div>
                        <small class="text-muted">
                            CC: {{ $s->cedula }} &nbsp;|&nbsp;
                            📞 {{ $s->telefono }} &nbsp;|&nbsp;
                            {{ $s->user->email ?? '' }}
                        </small>
                    </div>
                    <span class="badge badge-{{ $s->estado }} px-3 py-2 rounded-pill fw-bold">
                        @if($s->estado == 'pendiente') ⏳ Pendiente
                        @elseif($s->estado == 'aceptada') ✅ Aceptada
                        @else ❌ Rechazada @endif
                    </span>
                </div>

                <!-- MASCOTA -->
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3"
                     style="background:#f0fdf4; border:1.5px solid #d1fae5;">
                    <img src="{{ asset('storage/img/' . ($s->mascota->imagen ?? 'placeholder.png')) }}"
                         style="width:56px; height:56px; border-radius:50%; object-fit:cover; border:2px solid #16a34a;"
                         onerror="this.src='{{ asset('img/placeholder.png') }}'">
                    <div>
                        <div class="fw-bold" style="color:#14532d;">🐾 {{ $s->mascota->nombre ?? 'Mascota eliminada' }}</div>
                        <small class="text-muted">
                            {{ $s->mascota->edad ?? '' }} año(s) &nbsp;|&nbsp;
                            Solicitud: {{ $s->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>

                <!-- INFO RÁPIDA -->
                <div class="info-grid">
                    <div class="info-item">
                        <strong>🏠 Vivienda</strong>
                        <span>{{ ucfirst($s->tipo_vivienda) }}
                            {{ $s->tiene_patio ? '• Con patio' : '' }}
                            {{ $s->es_propia ? '• Propia' : '• Arrendada' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>👥 Personas en casa</strong>
                        <span>{{ $s->personas_en_casa }} persona(s)</span>
                    </div>
                    <div class="info-item">
                        <strong>👶 Niños</strong>
                        <span>{{ $s->tiene_ninos ? 'Sí — ' . ($s->edades_ninos ?? 'sin edades') : 'No' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>🐾 Otras mascotas</strong>
                        <span>{{ $s->tiene_otros_animales ? 'Sí — ' . ($s->cuales_animales ?? '') : 'No' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>🎓 Experiencia</strong>
                        <span>{{ $s->tiene_experiencia ? 'Con experiencia' : 'Sin experiencia' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>⏰ Horas sola</strong>
                        <span>{{ $s->horas_sola_mascota }} h/día
                            {{ $s->quien_cuida_ausencia ? '• ' . $s->quien_cuida_ausencia : '' }}
                        </span>
                    </div>
                </div>

                <!-- BOTÓN VER DETALLE COMPLETO -->
                <button class="btn-ver-detalle mb-3"
                        onclick="toggleDetalle({{ $s->id }}, this)">
                    👁 Ver formulario completo
                </button>

                <!-- DETALLE EXPANDIBLE -->
                <div class="detalle-completo" id="detalle-{{ $s->id }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detalle-titulo">📍 Dirección</div>
                            <div class="detalle-valor">{{ $s->direccion }}, {{ $s->ciudad }}</div>
                        </div>
                        @if($s->descripcion_experiencia)
                        <div class="col-md-6">
                            <div class="detalle-titulo">🎓 Descripción experiencia</div>
                            <div class="detalle-valor">{{ $s->descripcion_experiencia }}</div>
                        </div>
                        @endif
                        <div class="col-12">
                            <div class="detalle-titulo">💚 Motivo de adopción</div>
                            <div class="detalle-valor">{{ $s->motivo_adopcion }}</div>
                        </div>
                        @if($s->compromiso)
                        <div class="col-12">
                            <div class="detalle-titulo">🤝 Compromiso</div>
                            <div class="detalle-valor">{{ $s->compromiso }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ACCIONES O RESULTADO -->
                @if($s->estado == 'pendiente')
                <div class="form-respuesta mt-3">
                    <form action="/admin_adopciones/{{ $s->id }}/responder" method="POST">
                        @csrf
                        <label class="form-label fw-semibold small">
                            💬 Mensaje para el adoptante (opcional)
                        </label>
                        <textarea name="observaciones" class="form-control mb-3" rows="2"
                                  placeholder="Ej: Todo se ve bien, te esperamos. / Necesitamos más información sobre..."></textarea>
                        <div class="d-flex gap-2">
                            <button type="submit" name="estado" value="aceptada" class="btn-aceptar">
                                ✅ Aceptar solicitud
                            </button>
                            <button type="submit" name="estado" value="rechazada" class="btn-rechazar">
                                ❌ Rechazar
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="resultado-box {{ $s->estado }} mt-3">
                    <div class="emoji">{{ $s->estado == 'aceptada' ? '✅' : '❌' }}</div>
                    <div class="titulo">
                        {{ $s->estado == 'aceptada' ? 'Solicitud aceptada' : 'Solicitud rechazada' }}
                    </div>
                    <div class="fecha">{{ $s->updated_at?->format('d/m/Y H:i') }}</div>
                    @if($s->observaciones)
                        <div class="obs">💬 {{ $s->observaciones }}</div>
                    @endif
                    @if($s->estado == 'aceptada' && !$s->cita)
                        <a href="/admin_citas" class="btn btn-success btn-sm rounded-pill px-4 mt-3">
                            📅 Agendar cita
                        </a>
                    @elseif($s->cita)
                        <div class="mt-2 p-2 rounded-3" style="background:white; font-size:.82rem;">
                            📅 Cita agendada: <strong>{{ \Carbon\Carbon::parse($s->cita->fecha)->format('d/m/Y') }}</strong>
                            a las <strong>{{ $s->cita->hora }}</strong>
                            — 🔐 <code>{{ $s->cita->codigo_verificacion }}</code>
                        </div>
                    @endif
                </div>
                @endif

                {{-- Botón eliminar — visible para TODAS las solicitudes --}}
                <button type="button"
                        class="btn btn-sm btn-danger w-100 mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEliminarAdopcion{{ $s->id }}">
                    🗑️ Eliminar solicitud
                </button>

            </div>
        </div>
        @empty
        <div class="sin-resultados">
            <p style="font-size:4rem;">📋</p>
            <h5 class="fw-bold">Sin solicitudes todavía</h5>
            <p>Cuando alguien solicite adoptar una mascota aparecerá aquí.</p>
        </div>
        @endforelse

    </div>
</div>

{{-- MODALES DE ELIMINACIÓN — fuera de cualquier contenedor --}}
@foreach($solicitudes as $s)
<div class="modal fade" id="modalEliminarAdopcion{{ $s->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div style="font-size:3.5rem; line-height:1;">🗑️</div>
                <h5 class="fw-bold mt-3 mb-1">¿Eliminar solicitud?</h5>
                <p class="text-muted small mb-4">
                    Se eliminará la solicitud de adopción de
                    <strong>{{ $s->nombre_completo }}</strong>
                    para <strong>{{ $s->mascota->nombre ?? 'esta mascota' }}</strong>.<br>
                    <span class="text-danger fw-semibold">Esta acción no se puede deshacer.</span>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="/admin_adopciones/{{ $s->id }}">
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

function filtrar(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('.solicitud-item').forEach(el => {
        el.style.display = (estado === 'todos' || el.dataset.estado === estado) ? '' : 'none';
    });
}

function toggleDetalle(id, btn) {
    const detalle = document.getElementById('detalle-' + id);
    const visible = detalle.classList.toggle('visible');
    btn.textContent = visible ? '🔼 Ocultar detalle' : '👁 Ver formulario completo';
}

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

    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', cerrarMobile);
    });
})();
</script>
</body>
</html>