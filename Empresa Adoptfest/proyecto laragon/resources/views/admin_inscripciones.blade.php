<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripciones - Admin Adoptafest</title>
    <link rel="stylesheet" href="{{ asset('css/admin_inscripciones.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="tiene-sidebar">

@if(session('rol') == 'admin')
<aside class="admin-sidebar" id="adminSidebar">

    <!-- Logo / marca -->
    <div class="sidebar-brand">
        <img src="{{ asset('storage/img/logo de empresa.png') }}" alt="Logo" class="sidebar-logo">
        <span class="sidebar-brand-text">Adoptafest</span>
    </div>

    <!-- Botón colapsar -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Contraer menú" title="Contraer menú">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>

    <!-- Usuario actual -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">{{ strtoupper(substr(session('nombre', 'A'), 0, 1)) }}</div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ session('nombre') }}</span>
            <span class="sidebar-user-rol">Administrador</span>
        </div>
    </div>

    <!-- Navegación -->
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

    <!-- Pie -->
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

<!-- Botón flotante para abrir el menú en mobile -->
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
    <div class=".contenido">
        <h1>📋 Inscripciones a Eventos</h1>
        <p>Gestiona quién participa, acepta o rechaza solicitudes</p>
    </div>
</div>

<!-- CONTENIDO -->
<div class="contenido">
    <div class="container" style="max-width: 920px;">

        @if(session('exito'))
            <div class="alert alert-success rounded-3 mb-4">✅ {{ session('exito') }}</div>
        @endif

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero">{{ $inscripciones->count() }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#ffc107;">
                        {{ $inscripciones->where('estado','pendiente')->count() }}
                    </div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#4ade80;">
                        {{ $inscripciones->where('estado','aceptado')->count() }}
                    </div>
                    <div class="stat-label">Aceptados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#f87171;">
                        {{ $inscripciones->where('estado','rechazado')->count() }}
                    </div>
                    <div class="stat-label">Rechazados</div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filtros-wrapper">
            <button class="filtro-btn activo" onclick="filtrar('todos', this)">Todos</button>
            <button class="filtro-btn" onclick="filtrar('pendiente', this)">⏳ Pendientes</button>
            <button class="filtro-btn" onclick="filtrar('aceptado', this)">✓ Aceptados</button>
            <button class="filtro-btn" onclick="filtrar('rechazado', this)">✕ Rechazados</button>
        </div>

        <!-- LISTA -->
        @forelse($inscripciones as $i)
        @php
            $insignia = $i->user ? $i->user->insigniaDonante() : null;
        @endphp
        <div class="card-inscripcion inscripcion-item" data-estado="{{ $i->estado }}">
            <div class="barra-estado barra-{{ $i->estado }}"></div>
            <div class="p-4">
                <div class="row align-items-start g-4">

                    <!-- INFO -->
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-user">
                                {{ strtoupper(substr($i->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6 d-flex align-items-center gap-2">
                                    {{ $i->user->name ?? 'Usuario eliminado' }}
                                    @if($insignia)
                                        <span class="badge-donante"
                                              style="background: {{ $insignia['color'] }}1a; color: {{ $insignia['color'] }}; border: 1px solid {{ $insignia['color'] }}40;"
                                              title="{{ $insignia['etiqueta'] }}">
                                            {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $i->user->email ?? '' }}</small>
                            </div>
                            <span class="badge badge-{{ $i->estado }} px-3 py-2 rounded-pill">
                                @if($i->estado == 'pendiente') ⏳
                                @elseif($i->estado == 'aceptado') ✓
                                @else ✕ @endif
                                {{ ucfirst($i->estado) }}
                            </span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-primary rounded-pill px-3">
                                🎉 {{ $i->evento->titulo ?? 'Evento eliminado' }}
                            </span>
                            @if($i->evento)
                                <span class="badge bg-light text-dark border rounded-pill">
                                    📅 {{ $i->evento->fecha }}
                                </span>
                            @endif
                            @if($i->telefono)
                                <span class="badge bg-light text-dark border rounded-pill">
                                    📞 {{ $i->telefono }}
                                </span>
                            @endif
                        </div>

                        @if($i->comentario)
                            <div class="bg-light rounded-3 p-3 mb-3">
                                <small class="text-muted fst-italic">"{{ $i->comentario }}"</small>
                            </div>
                        @endif

                        @php
                            $mes = \App\Models\MascotaEvento::with('mascota')
                                ->where('inscripcion_id', $i->id)->get();
                        @endphp
                        @if($mes->count())
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <small class="fw-semibold text-muted">🐾 Mascotas:</small>
                                @foreach($mes as $me)
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        {{ $me->mascota->nombre ?? '?' }}
                                        <span class="ms-1 @if(($me->mascota->estado ?? '') == 'en_evento') text-success @else text-primary @endif">
                                            ({{ $me->mascota->estado ?? '' }})
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">Sin mascotas registradas</small>
                        @endif

                        @if($i->observaciones)
                            <div class="mt-3 p-3 rounded-3 border-start border-3 border-warning bg-light">
                                <small><strong>💬 Observación:</strong> {{ $i->observaciones }}</small>
                            </div>
                        @endif
                    </div>

                    <!-- ACCIONES -->
                    <div class="col-lg-5">
                        @if($i->estado == 'pendiente')
                        <form method="POST" action="/admin_inscripciones/{{ $i->id }}">
                            @csrf
                            <label class="form-label fw-semibold small">Observaciones (opcional)</label>
                            <textarea name="observaciones" class="form-control form-control-sm mb-3"
                                      rows="3" placeholder="Escribe un mensaje para el usuario..."></textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" name="estado" value="aceptado" class="btn-aceptar">
                                    ✓ Aceptar
                                </button>
                                <button type="submit" name="estado" value="rechazado" class="btn-rechazar">
                                    ✕ Rechazar
                                </button>
                            </div>
                        </form>

                        @else
                        <div class="text-center p-4 rounded-3"
                             style="background: {{ $i->estado == 'aceptado' ? '#d1fae5' : '#fee2e2' }}">
                            <div style="font-size: 2.5rem;">
                                {{ $i->estado == 'aceptado' ? '✅' : '❌' }}
                            </div>
                            <div class="fw-semibold mt-1">
                                {{ $i->estado == 'aceptado' ? 'Inscripción aceptada' : 'Inscripción rechazada' }}
                            </div>
                            <small class="text-muted">{{ $i->updated_at?->format('d/m/Y H:i') }}</small>
                        </div>
                        @endif

                        {{-- Botón eliminar  --}}
                        <button type="button"
                                class="btn btn-sm btn-danger w-100 mt-3"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminar{{ $i->id }}">
                            🗑️ Eliminar inscripción
                        </button>
                    </div>

                </div>
            </div>
        </div>

        @empty
        <div class="text-center py-5" style="color: white;">
            <p style="font-size: 4rem;">📋</p>
            <p class="fs-5">No hay inscripciones todavía.</p>
            <a href="/eventos" class="btn btn-success mt-2">Ver eventos</a>
        </div>
        @endforelse

    </div>
</div>

{{-- MODALES DE ELIMINACIÓN --}}
@foreach($inscripciones as $i)
<div class="modal fade" id="modalEliminar{{ $i->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div style="font-size:3.5rem; line-height:1;">🗑️</div>
                <h5 class="fw-bold mt-3 mb-1">¿Eliminar inscripción?</h5>
                <p class="text-muted small mb-4">
                    Se eliminará la inscripción de
                    <strong>{{ $i->user->name ?? 'este usuario' }}</strong>
                    al evento <strong>{{ $i->evento->titulo ?? 'este evento' }}</strong>.<br>
                    <span class="text-danger fw-semibold">Esta acción no se puede deshacer.</span>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="/admin_inscripciones/{{ $i->id }}">
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
    document.querySelectorAll('.inscripcion-item').forEach(el => {
        el.style.display = (estado === 'todos' || el.dataset.estado === estado) ? '' : 'none';
    });
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

    function abrirMobile() {
        sidebar.classList.add('abierto-mobile');
        overlay.classList.add('visible');
    }
    function cerrarMobile() {
        sidebar.classList.remove('abierto-mobile');
        overlay.classList.remove('visible');
    }
    mobileToggle.addEventListener('click', abrirMobile);
    overlay.addEventListener('click', cerrarMobile);

    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', cerrarMobile);
    });
})();
</script>
</body>
</html>