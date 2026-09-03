<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Donaciones - Adoptafest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/admin_donaciones.css') }}">
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
        <h1>💸 Administrar Donaciones</h1>
        <p>Aprueba insumos para agendar su recolección y consulta el historial de pagos</p>
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
                    <div class="stat-numero" style="color:#ffc107;">
                        {{ $especies->where('estado','pendiente')->count() }}
                    </div>
                    <div class="stat-label">Insumos pendientes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#60a5fa;">
                        {{ $especies->where('estado','aprobado')->count() }}
                    </div>
                    <div class="stat-label">Esperando recolección</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero" style="color:#4ade80;">
                        {{ $especies->where('estado','confirmado')->count() }}
                    </div>
                    <div class="stat-label">Insumos confirmados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-numero">
                        ${{ number_format($dineros->sum('monto'), 2) }}
                    </div>
                    <div class="stat-label">Total recaudado</div>
                </div>
            </div>
        </div>

        @if($especies->where('estado','aprobado')->count() > 0)
        <div class="alert alert-primary border-0 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                🚚 Tienes <strong>{{ $especies->where('estado','aprobado')->count() }}</strong>
                donación(es) aprobadas esperando que agendes la recolección.
            </span>
            <a href="/admin_citas" class="btn btn-sm btn-primary rounded-pill px-3">Ir a Citas →</a>
        </div>
        @endif

        <!-- FILTROS -->
        <div class="filtros-wrapper">
            <span style="font-size:.85rem; font-weight:600; color:rgba(255,255,255,0.8);">Filtrar insumos:</span>
            <button class="filtro-btn activo" onclick="filtrarEspecie('todos', this)">Todos</button>
            <button class="filtro-btn" onclick="filtrarEspecie('pendiente', this)">⏳ Pendientes</button>
            <button class="filtro-btn" onclick="filtrarEspecie('aprobado', this)">🚚 Esperando recolección</button>
            <button class="filtro-btn" onclick="filtrarEspecie('confirmado', this)">✓ Confirmados</button>
            <button class="filtro-btn" onclick="filtrarEspecie('rechazado', this)">✕ Rechazados</button>
        </div>

        <!-- ══ INSUMOS ══ -->
        @forelse($especies as $e)
        @php
            $insignia    = $e->user ? $e->user->insigniaDonante() : null;
            $claseEstado = $e->estado == 'pendiente' ? 'pendiente' : ($e->estado == 'rechazado' ? 'rechazado' : 'aceptado');
        @endphp
        <div class="card-solicitud especie-item" data-estado="{{ $e->estado }}">
            <div class="barra-estado barra-{{ $claseEstado }}"></div>
            <div class="p-4">

                <!-- CABECERA -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-user">
                        {{ strtoupper(substr($e->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-6 d-flex align-items-center gap-2 flex-wrap">
                            {{ $e->user->name ?? 'Usuario eliminado' }}
                            @if($insignia)
                                <span class="badge-donante" style="background:{{ $insignia['color'] }}1a;color:{{ $insignia['color'] }};border:1px solid {{ $insignia['color'] }}40;">
                                    {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                </span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $e->user->email ?? '' }}</small>
                    </div>
                    <span class="badge badge-{{ $claseEstado }} px-3 py-2 rounded-pill fw-bold">
                        @if($e->estado == 'pendiente') ⏳ Pendiente
                        @elseif($e->estado == 'aprobado') 🚚 Esperando recolección
                        @elseif($e->estado == 'confirmado') ✓ Confirmado
                        @else ✕ Rechazado @endif
                    </span>
                </div>

                <!-- DETALLE DEL INSUMO -->
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3"
                     style="background:#f0fdf4; border:1.5px solid #d1fae5;">
                    <div style="font-size:2rem;">📦</div>
                    <div>
                        <div class="fw-bold" style="color:#14532d;">
                            {{ ucfirst(str_replace('_',' ', $e->categoria)) }} — {{ $e->cantidad }} unid.
                        </div>
                        <small class="text-muted">
                            @if($e->especie_destino == 'perro') 🐶 Para perro
                            @elseif($e->especie_destino == 'gato') 🐱 Para gato
                            @elseif($e->especie_destino == 'otro') 🐰 Para otro animal
                            @else — @endif
                            &nbsp;|&nbsp; Registrado: {{ $e->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>

                <!-- INFO RÁPIDA -->
                <div class="info-grid">
                    <div class="info-item">
                        <strong>📍 Dirección de recolección</strong>
                        <span>{{ $e->direccion_recoleccion }}</span>
                    </div>
                    <div class="info-item">
                        <strong>📞 Teléfono de contacto</strong>
                        <span>{{ $e->telefono_contacto }}</span>
                    </div>
                    @if($e->descripcion)
                    <div class="info-item">
                        <strong>📝 Descripción</strong>
                        <span>{{ $e->descripcion }}</span>
                    </div>
                    @endif
                </div>

                <!-- ACCIONES O RESULTADO -->
                @if($e->estado == 'pendiente')
                <div class="form-respuesta mt-3">
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin_donaciones.especie.aceptar', $e->id) }}">
                            @csrf
                            <button type="submit" class="btn-aceptar">✓ Aceptar</button>
                        </form>
                        <form method="POST" action="{{ route('admin_donaciones.especie.rechazar', $e->id) }}">
                            @csrf
                            <button type="submit" class="btn-rechazar">✕ Rechazar</button>
                        </form>
                    </div>
                </div>
                @elseif($e->estado == 'aprobado')
                <div class="resultado-box aceptado mt-3">
                    <div class="emoji">🚚</div>
                    <div class="titulo">Esperando agendar recolección</div>
                    <a href="/admin_citas" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                        📅 Ir a Citas
                    </a>
                </div>
                @elseif($e->estado == 'confirmado')
                <div class="resultado-box aceptado mt-3">
                    <div class="emoji">✅</div>
                    <div class="titulo">Donación confirmada</div>
                    <div class="fecha">{{ $e->confirmado_at?->format('d/m/Y H:i') }}</div>
                </div>
                @else
                <div class="resultado-box rechazado mt-3">
                    <div class="emoji">❌</div>
                    <div class="titulo">Donación rechazada</div>
                    <div class="fecha">{{ $e->updated_at?->format('d/m/Y H:i') }}</div>
                </div>
                @endif

                {{-- Botón eliminar  --}}
                <button type="button"
                        class="btn btn-sm btn-danger w-100 mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEliminarEspecie{{ $e->id }}">
                    🗑️ Eliminar donación
                </button>

            </div>
        </div>
        @empty
        <div class="sin-resultados">
            <p style="font-size:4rem;">📦</p>
            <h5 class="fw-bold">Sin donaciones en especie todavía</h5>
            <p>Cuando alguien registre una donación de insumos aparecerá aquí.</p>
        </div>
        @endforelse

        <!-- ══ HISTORIAL DE PAGOS ══ -->
        <h5 class="fw-bold mt-5 mb-3 text-white">💵 Donaciones en dinero confirmadas</h5>

        @if($dineros->count() > 0)
        <div class="table-responsive">
            <table class="table tabla-donaciones align-middle">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>ID de orden PayPal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dineros as $d)
                    @php $insignia = $d->user ? $d->user->insigniaDonante() : null; @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $d->user->name ?? 'Usuario eliminado' }}</div>
                            @if($insignia)
                                <span class="badge-donante" style="background:{{ $insignia['color'] }}1a;color:{{ $insignia['color'] }};border:1px solid {{ $insignia['color'] }}40;">
                                    {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                </span>
                            @endif
                        </td>
                        <td class="fw-bold text-success">${{ number_format($d->monto, 2) }} {{ $d->moneda }}</td>
                        <td class="small text-muted">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                        <td class="small text-muted">{{ $d->paypal_order_id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="sin-resultados">
            <p style="font-size:3rem;">💵</p>
            <p class="text-white-50">Aún no hay donaciones en dinero confirmadas.</p>
        </div>
        @endif

    </div>
</div>

{{-- MODALES DE ELIMINACIÓN  --}}
@foreach($especies as $e)
<div class="modal fade" id="modalEliminarEspecie{{ $e->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div style="font-size:3.5rem; line-height:1;">🗑️</div>
                <h5 class="fw-bold mt-3 mb-1">¿Eliminar donación?</h5>
                <p class="text-muted small mb-4">
                    Se eliminará la donación de
                    <strong>{{ $e->user->name ?? 'este usuario' }}</strong>
                    — <strong>{{ ucfirst(str_replace('_',' ', $e->categoria)) }}, {{ $e->cantidad }} unid.</strong><br>
                    <span class="text-danger fw-semibold">Esta acción no se puede deshacer.</span>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="/admin_donaciones/especie/{{ $e->id }}">
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
function filtrarEspecie(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('.especie-item').forEach(el => {
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

    function abrirMobile() { sidebar.classList.add('abierto-mobile'); overlay.classList.add('visible'); }
    function cerrarMobile() { sidebar.classList.remove('abierto-mobile'); overlay.classList.remove('visible'); }
    mobileToggle.addEventListener('click', abrirMobile);
    overlay.addEventListener('click', cerrarMobile);
    sidebar.querySelectorAll('.sidebar-link').forEach(link => link.addEventListener('click', cerrarMobile));
})();
</script>
</body>
</html>