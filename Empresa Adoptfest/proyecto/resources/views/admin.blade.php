<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Usuarios - Adoptafest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
<div class="hero-admin">
    <h1>👥 Administrar Usuarios</h1>
    <p>Gestiona clientes y administradores de Adoptafest</p>
</div>

<!-- CONTENIDO -->
<div class="container tabla-usuarios-container">
    <div class="contenido">
    <div class="container" style="max-width: 1500px;">

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

    <!-- STATS -->
    <div class="stats-row justify-content-center">
        <div class="stat-pill">
            <div class="dot" style="background:#198754;"></div>
            <strong>{{ $usuarios->count() }}</strong>
        </div>
        <div class="stat-pill">
            <div class="dot" style="background:#1f2937;"></div>
            Admins: <strong>{{ $usuarios->where('rol','admin')->count() }}</strong>
        </div>
        <div class="stat-pill">
            <div class="dot" style="background:#16a34a;"></div>
            Clientes: <strong>{{ $usuarios->where('rol','cliente')->count() }}</strong>
        </div>
    </div>

    <!-- BUSCADOR Y FILTROS -->
    <div class="buscador-wrapper">
        <form method="GET" action="/admin" id="formBuscar">
            <div class="row g-3 align-items-end">

                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-semibold small text-muted">🔍 Buscar por nombre o correo</label>
                    <input type="text"
                           name="buscar"
                           class="form-control"
                           placeholder="Escribe un nombre o correo..."
                           value="{{ request('buscar') }}">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted">🏷️ Filtrar por rol</label>
                    <select name="rol" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="cliente" {{ request('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                        <option value="admin"   {{ request('rol') == 'admin'   ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn-buscar flex-fill">🔍 Buscar</button>
                    @if(request('buscar') || request('rol'))
                        <a href="/admin" class="btn-limpiar">✕ Limpiar</a>
                    @endif
                </div>

            </div>

            @if(request('buscar') || request('rol'))
                <div class="mt-3 small text-muted">
                    Mostrando resultados para:
                    @if(request('buscar'))
                        <span class="badge bg-success">{{ request('buscar') }}</span>
                    @endif
                    @if(request('rol'))
                        <span class="badge bg-dark">{{ ucfirst(request('rol')) }}</span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    <!-- TABLA -->
    @if(count($usuarios) > 0)
    <div class="table-responsive">
        <table class="tabla-usuarios align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Nivel donante</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($usuarios as $usuario)
            @php $insignia = $usuario->insigniaDonante(); @endphp
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="circulo-usuario">
                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                        </div>
                        <span class="fw-semibold">{{ $usuario->name }}</span>
                    </div>
                </td>

                <td class="text-muted small">{{ $usuario->email }}</td>

                <td class="text-muted small">{{ $usuario->telefono ?? '—' }}</td>

                <td>
                    @if($usuario->rol == 'admin')
                        <span class="badge-admin">Admin</span>
                    @else
                        <span class="badge-cliente">Cliente</span>
                    @endif
                </td>

                <!-- COLUMNA NIVEL DONANTE -->
                <td>
                    @if($insignia)
                        <span class="badge-donante"
                              style="background:{{ $insignia['color'] }}1a; color:{{ $insignia['color'] }}; border:1px solid {{ $insignia['color'] }}40;">
                            {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                        </span>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>

                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn-ver"
                                data-bs-toggle="modal"
                                data-bs-target="#verUsuario{{ $usuario->id }}">
                            👁 Ver
                        </button>
                        <button class="btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editarUsuario{{ $usuario->id }}">
                            ✏️ Editar
                        </button>
                        @if($usuario->id !== session('id'))
                        <button class="btn-del"
                                data-bs-toggle="modal"
                                data-bs-target="#eliminarUsuario{{ $usuario->id }}">
                            🗑 Eliminar
                        </button>
                        @else
                        <span class="text-muted small" style="font-size:0.75rem;">Tú mismo</span>
                        @endif
                    </div>
                </td>

                <!-- ══ MODAL VER ══ -->
                <div class="modal fade" id="verUsuario{{ $usuario->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-header-ver">
                                <h5 class="modal-title">👁 Información del usuario</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <div class="circulo-usuario mx-auto mb-2"
                                         style="width:60px;height:60px;font-size:1.5rem;">
                                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                    </div>
                                    <div class="fw-bold fs-5">{{ $usuario->name }}</div>
                                    @if($usuario->rol == 'admin')
                                        <span class="badge-admin">Admin</span>
                                    @else
                                        <span class="badge-cliente">Cliente</span>
                                    @endif
                                    @if($insignia)
                                        <div class="mt-2">
                                            <span class="badge-donante"
                                                  style="background:{{ $insignia['color'] }}1a; color:{{ $insignia['color'] }}; border:1px solid {{ $insignia['color'] }}40;">
                                                {{ $insignia['emoji'] }} {{ $insignia['etiqueta'] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="info-row">
                                    <span class="info-label">📧 Correo</span>
                                    <span class="info-valor">{{ $usuario->email }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">📞 Teléfono</span>
                                    <span class="info-valor">{{ $usuario->telefono ?? 'No registrado' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">🗓 Registro</span>
                                    <span class="info-valor">{{ $usuario->created_at?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                                @if($usuario->descripcion)
                                <div class="info-row">
                                    <span class="info-label">📝 Descripción</span>
                                    <span class="info-valor">{{ $usuario->descripcion }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary btn-sm rounded-pill px-4"
                                        data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MODAL EDITAR ══ -->
                <div class="modal fade" id="editarUsuario{{ $usuario->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="/admin/{{ $usuario->id }}" method="POST"
                                  id="formEditar{{ $usuario->id }}" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="modal-header modal-header-editar">
                                    <h5 class="modal-title">✏️ Editar usuario</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    <div class="mb-3">
                                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                               id="e_nombre_{{ $usuario->id }}"
                                               class="form-control"
                                               value="{{ $usuario->name }}"
                                               minlength="3" maxlength="100">
                                        <div class="invalid-feedback">⚠️ El nombre es obligatorio (mínimo 3 caracteres).</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                        <input type="email" name="email"
                                               id="e_email_{{ $usuario->id }}"
                                               class="form-control"
                                               value="{{ $usuario->email }}">
                                        <div class="invalid-feedback">⚠️ Ingresa un correo válido, ej: nombre@correo.com</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" name="telefono"
                                               id="e_tel_{{ $usuario->id }}"
                                               class="form-control"
                                               value="{{ $usuario->telefono }}"
                                               placeholder="Ej: 3001234567"
                                               inputmode="numeric" maxlength="10"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <div class="invalid-feedback">⚠️ Solo se permiten números (máx. 10 dígitos).</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <select name="rol" class="form-select">
                                            <option value="cliente" {{ $usuario->rol == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                            <option value="admin"   {{ $usuario->rol == 'admin'   ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </div>

                                    <div class="mb-1">
                                        <label class="form-label">
                                            Nueva contraseña
                                            <span class="text-muted fw-normal">(dejar vacío para no cambiar)</span>
                                        </label>
                                        <input type="password" name="password"
                                               id="e_pass_{{ $usuario->id }}"
                                               class="form-control"
                                               placeholder="••••••••"
                                               minlength="6">
                                        <div class="invalid-feedback">⚠️ La contraseña debe tener al menos 6 caracteres.</div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                                            data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold">
                                        💾 Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══ MODAL ELIMINAR ══ -->
                @if($usuario->id !== session('id'))
                <div class="modal fade" id="eliminarUsuario{{ $usuario->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-header-borrar">
                                <h5 class="modal-title">🗑 Eliminar usuario</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="eliminar-aviso">
                                    <p class="mb-1 fw-semibold">⚠️ Esta acción no se puede deshacer.</p>
                                    <p class="mb-0 text-muted small">
                                        Estás a punto de eliminar a
                                        <strong>{{ $usuario->name }}</strong>
                                        ({{ $usuario->email }}).
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                                        data-bs-dismiss="modal">Cancelar</button>
                                <form action="/admin/{{ $usuario->id }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">
                                        🗑 Sí, eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if(method_exists($usuarios, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $usuarios->appends(request()->query())->links() }}
        </div>
    @endif

    @else
    <div class="sin-resultados">
        <p style="font-size:3rem;">🔍</p>
        <h5 class="fw-bold text-muted">Sin resultados</h5>
        <p class="text-muted">
            No se encontraron usuarios con
            @if(request('buscar')) "<strong>{{ request('buscar') }}</strong>"@endif
            @if(request('rol')) de rol <strong>{{ request('rol') }}</strong>@endif.
        </p>
        <a href="/admin" class="btn btn-success rounded-pill px-4">Ver todos</a>
    </div>
    @endif

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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

<script>
document.querySelectorAll('[id^="editarUsuario"]').forEach(function(modal) {
    const form = modal.querySelector('form');
    if (!form) return;

    const id     = modal.id.replace('editarUsuario', '');
    const nombre = document.getElementById('e_nombre_' + id);
    const email  = document.getElementById('e_email_'  + id);
    const tel    = document.getElementById('e_tel_'    + id);
    const pass   = document.getElementById('e_pass_'   + id);

    form.addEventListener('submit', function(e) {
        let valido = true;

        if (!nombre.value.trim() || nombre.value.trim().length < 3) {
            nombre.classList.add('is-invalid'); valido = false;
        } else { nombre.classList.remove('is-invalid'); nombre.classList.add('is-valid'); }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
            email.classList.add('is-invalid'); valido = false;
        } else { email.classList.remove('is-invalid'); email.classList.add('is-valid'); }

        if (tel.value.trim() !== '' && !/^\d{1,10}$/.test(tel.value.trim())) {
            tel.classList.add('is-invalid'); valido = false;
        } else { tel.classList.remove('is-invalid'); }

        if (pass.value !== '' && pass.value.length < 6) {
            pass.classList.add('is-invalid'); valido = false;
        } else { pass.classList.remove('is-invalid'); }

        if (!valido) {
            e.preventDefault();
            const primerError = form.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    [nombre, email, tel, pass].forEach(function(campo) {
        campo.addEventListener('input', function() {
            campo.classList.remove('is-invalid', 'is-valid');
        });
    });
});
</script>
</body>
</html>