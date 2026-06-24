<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Adoptafest - Admin Eventos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/admin_eventos.css') }}">
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

<!-- CONTENIDO -->
<div class="container tabla-usuarios-container">

  <div class="titulo-admin text-center mb-4">
    <h1>📅 Administrar Eventos</h1>
    <p>Gestiona los eventos de adopción y actividades</p>
  </div>

  @if(session('exito'))
    <div class="alert alert-success rounded-3 mb-3">✅ {{ session('exito') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger rounded-3 mb-3">❌ {{ session('error') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3">
      <strong>⚠️ Hay campos incompletos o incorrectos:</strong>
      <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- STATS -->
  <div class="stats-row justify-content-center">
    <div class="stat-pill">
      <div class="dot" style="background:#374151;"></div>
      Total: <strong>{{ $eventos->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#16a34a;"></div>
      Activos: <strong>{{ $eventos->where('estado','activo')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#dc2626;"></div>
      Cancelados: <strong>{{ $eventos->where('estado','cancelado')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#6b7280;"></div>
      Finalizados: <strong>{{ $eventos->where('estado','finalizado')->count() }}</strong>
    </div>
  </div>

  <!-- BUSCADOR -->
  <div class="buscador-wrapper">
    <form method="GET" action="/admin_eventos" id="formBuscar">
      <div class="row g-3 align-items-end">

        <div class="col-lg-4 col-md-6">
          <label class="form-label fw-semibold small text-muted">🔍 Buscar por título o lugar</label>
          <input type="text" name="buscar" class="form-control"
                 placeholder="Ej: Feria de adopción..."
                 value="{{ request('buscar') }}">
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label fw-semibold small text-muted">📌 Estado</label>
          <select name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="activo"     {{ request('estado')=='activo'     ? 'selected':'' }}>Activo</option>
            <option value="cancelado"  {{ request('estado')=='cancelado'  ? 'selected':'' }}>Cancelado</option>
            <option value="finalizado" {{ request('estado')=='finalizado' ? 'selected':'' }}>Finalizado</option>
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label fw-semibold small text-muted">🏷️ Categoría</label>
          <select name="categoria" class="form-select">
            <option value="">Todas</option>
            <option value="adopcion"   {{ request('categoria')=='adopcion'   ? 'selected':'' }}>🐾 Adopción</option>
            <option value="educacion"  {{ request('categoria')=='educacion'  ? 'selected':'' }}>📘 Educación</option>
            <option value="recreacion" {{ request('categoria')=='recreacion' ? 'selected':'' }}>🎉 Recreación</option>
          </select>
        </div>

        <div class="col-lg-4 d-flex gap-2 align-items-end">
          <button type="submit" class="btn-buscar flex-fill">🔍 Buscar</button>
          @if(request('buscar') || request('estado') || request('categoria'))
            <a href="/admin_eventos" class="btn-limpiar">✕ Limpiar</a>
          @endif
          <button type="button" class="btn-crear ms-auto"
                  data-bs-toggle="modal" data-bs-target="#crearEvento">
            ➕ Crear
          </button>
        </div>

      </div>
    </form>
  </div>

  <!-- TABLA -->
  @if($eventos->count() > 0)
  <div class="table-responsive">
    <table class="table tabla-mascotas align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Imagen</th>
          <th>Título</th>
          <th>Fecha</th>
          <th>Lugar</th>
          <th>Categoría</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($eventos as $evento)
        <tr>
          <td class="text-muted small">{{ $evento->id }}</td>
          <td>
            <img src="{{ $evento->imagen ? asset('storage/img/'.$evento->imagen) : asset('storage/img/placeholder.png') }}"
                 class="img-tabla"
                 onerror="this.src='{{ asset('storage/img/placeholder.png') }}'">
          </td>
          <td class="fw-semibold">{{ $evento->titulo }}</td>
          <td class="small text-muted">
            {{ $evento->fecha ? \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y H:i') : '—' }}
          </td>
          <td class="small">{{ $evento->lugar ?? '—' }}</td>
          <td>
            @if($evento->categoria == 'adopcion')
              <span class="badge-adopcion">🐾 Adopción</span>
            @elseif($evento->categoria == 'educacion')
              <span class="badge-educacion">📘 Educación</span>
            @else
              <span class="badge-recreacion">🎉 Recreación</span>
            @endif
          </td>
          <td>
            @if($evento->estado == 'activo')
              <span class="badge-activo">● Activo</span>
            @elseif($evento->estado == 'cancelado')
              <span class="badge-cancelado">✕ Cancelado</span>
            @else
              <span class="badge-finalizado">✓ Finalizado</span>
            @endif
          </td>
          <td>
            <div class="d-flex gap-2 flex-wrap">
              <button class="btn-ver2"  data-bs-toggle="modal" data-bs-target="#verEvento{{ $evento->id }}">👁 Ver</button>
              <button class="btn-edit2" data-bs-toggle="modal" data-bs-target="#editarEvento{{ $evento->id }}">✏️ Editar</button>
              <button class="btn-del2"  data-bs-toggle="modal" data-bs-target="#eliminarEvento{{ $evento->id }}">🗑 Eliminar</button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="sin-resultados">
    <p style="font-size:3rem;">📅</p>
    <h5 class="fw-bold text-muted">Sin resultados</h5>
    <p class="text-muted">No se encontraron eventos con los filtros aplicados.</p>
    <a href="/admin_eventos" class="btn btn-success rounded-pill px-4">Ver todos</a>
  </div>
  @endif

</div>

{{-- ══ MODALES ══ --}}

<!-- ══ MODAL CREAR ══ -->
<div class="modal fade" id="crearEvento" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formCrearEvento" action="{{ route('admin_eventos.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="modal-header mh-crear">
          <h5 class="modal-title">➕ Crear nuevo evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          {{-- Título --}}
          <div class="mb-3">
            <label class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" name="titulo" id="crear_titulo" class="form-control"
                   placeholder="Ej: Feria de adopción Bogotá"
                   minlength="3" maxlength="200">
            <div class="invalid-feedback">⚠️ El título es obligatorio (mínimo 3 caracteres).</div>
          </div>

          {{-- Fecha --}}
          <div class="mb-3">
            <label class="form-label">Fecha y hora <span class="text-danger">*</span></label>
            <input type="datetime-local" name="fecha" id="crear_fecha" class="form-control">
            <small class="text-muted">Horario permitido: 8:00 AM – 8:00 PM.</small>
            <div class="invalid-feedback" id="err_crear_fecha_msg">⚠️ Selecciona una fecha futura en horario permitido (8:00 AM – 8:00 PM).</div>
          </div>

          {{-- Lugar --}}
          <div class="mb-3">
            <label class="form-label">Lugar <span class="text-danger">*</span></label>
            <input type="text" name="lugar" id="crear_lugar" class="form-control"
                   placeholder="Ej: Parque El Tunal, Bogotá" maxlength="100">
            <div class="invalid-feedback">⚠️ El lugar es obligatorio.</div>
          </div>

          {{-- Descripción --}}
          <div class="mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <textarea name="descripcion" id="crear_descripcion" class="form-control" rows="3"
                      placeholder="Describe el evento..." maxlength="500"></textarea>
            <div class="invalid-feedback">⚠️ La descripción es obligatoria.</div>
          </div>

          {{-- Categoría --}}
          <div class="mb-3">
            <label class="form-label">Categoría <span class="text-danger">*</span></label>
            <select name="categoria" id="crear_categoria" class="form-select">
              <option value="">— Selecciona una categoría —</option>
              <option value="adopcion">🐾 Adopción</option>
              <option value="educacion">📘 Educación</option>
              <option value="recreacion">🎉 Recreación</option>
            </select>
            <div class="invalid-feedback">⚠️ Debes seleccionar una categoría.</div>
          </div>

          {{-- Imagen --}}
          <div class="mb-3">
            <label class="form-label">Imagen <span class="text-danger">*</span></label>
            <input type="file" name="imagen" id="crear_imagen" class="form-control" accept="image/*"
                   onchange="previewEvento(this,'prevCrearEvento')">
            <div class="invalid-feedback">⚠️ Debes subir una imagen del evento.</div>
            <img id="prevCrearEvento" class="img-preview-evento mt-2" style="display:none;" alt="Preview">
          </div>

          <input type="hidden" name="estado" value="activo">
          <div class="alert alert-success border-0 small py-2 mb-0">
            ✅ El estado se fija en <strong>Activo</strong> al crear.
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">💾 Crear evento</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($eventos as $evento)

<!-- ══ MODAL VER ══ -->
<div class="modal fade" id="verEvento{{ $evento->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header mh-ver">
        <h5 class="modal-title">👁 Información del evento</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <img src="{{ $evento->imagen ? asset('storage/img/'.$evento->imagen) : asset('storage/img/placeholder.png') }}"
               class="img-preview-evento"
               onerror="this.src='{{ asset('storage/img/placeholder.png') }}'">
          <div class="fw-bold fs-5 mt-2">{{ $evento->titulo }}</div>
        </div>
        <div class="info-row">
          <span class="info-label">📅 Fecha</span>
          <span class="info-valor">{{ $evento->fecha ? \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y H:i') : '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">📍 Lugar</span>
          <span class="info-valor">{{ $evento->lugar ?? '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">🏷️ Categoría</span>
          <span class="info-valor">{{ ucfirst($evento->categoria) }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">📌 Estado</span>
          <span class="info-valor">
            @if($evento->estado=='activo') <span class="badge-activo">● Activo</span>
            @elseif($evento->estado=='cancelado') <span class="badge-cancelado">✕ Cancelado</span>
            @else <span class="badge-finalizado">✓ Finalizado</span>
            @endif
          </span>
        </div>
        @if($evento->descripcion)
        <div class="info-row">
          <span class="info-label">📝 Descripción</span>
          <span class="info-valor">{{ $evento->descripcion }}</span>
        </div>
        @endif
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL EDITAR ══ -->
<div class="modal fade" id="editarEvento{{ $evento->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formEditarEvento{{ $evento->id }}"
            action="{{ route('admin_eventos.update', $evento->id) }}" method="POST"
            enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <div class="modal-header mh-editar">
          <h5 class="modal-title">✏️ Editar — {{ $evento->titulo }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          {{-- Título --}}
          <div class="mb-3">
            <label class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" name="titulo" id="edit_titulo_{{ $evento->id }}" class="form-control"
                   value="{{ $evento->titulo }}" minlength="3" maxlength="200">
            <div class="invalid-feedback">⚠️ El título es obligatorio (mínimo 3 caracteres).</div>
          </div>

          {{-- Fecha --}}
          <div class="mb-3">
            <label class="form-label">Fecha y hora <span class="text-danger">*</span></label>
            <input type="datetime-local" name="fecha" id="edit_fecha_{{ $evento->id }}" class="form-control"
                   value="{{ $evento->fecha ? \Carbon\Carbon::parse($evento->fecha)->format('Y-m-d\TH:i') : '' }}">
            <small class="text-muted">Horario permitido: 8:00 AM – 8:00 PM.</small>
            <div class="invalid-feedback">⚠️ Selecciona una fecha futura en horario permitido (8:00 AM – 8:00 PM).</div>
          </div>

          {{-- Lugar --}}
          <div class="mb-3">
            <label class="form-label">Lugar <span class="text-danger">*</span></label>
            <input type="text" name="lugar" id="edit_lugar_{{ $evento->id }}" class="form-control"
                   value="{{ $evento->lugar }}" maxlength="100">
            <div class="invalid-feedback">⚠️ El lugar es obligatorio.</div>
          </div>

          {{-- Descripción --}}
          <div class="mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <textarea name="descripcion" id="edit_desc_{{ $evento->id }}" class="form-control"
                      rows="3" maxlength="500">{{ $evento->descripcion }}</textarea>
            <div class="invalid-feedback">⚠️ La descripción es obligatoria.</div>
          </div>

          {{-- Categoría --}}
          <div class="mb-3">
            <label class="form-label">Categoría <span class="text-danger">*</span></label>
            <select name="categoria" id="edit_cat_{{ $evento->id }}" class="form-select">
              <option value="">— Selecciona una categoría —</option>
              <option value="adopcion"   {{ $evento->categoria=='adopcion'   ? 'selected':'' }}>🐾 Adopción</option>
              <option value="educacion"  {{ $evento->categoria=='educacion'  ? 'selected':'' }}>📘 Educación</option>
              <option value="recreacion" {{ $evento->categoria=='recreacion' ? 'selected':'' }}>🎉 Recreación</option>
            </select>
            <div class="invalid-feedback">⚠️ Debes seleccionar una categoría.</div>
          </div>

          {{-- Estado --}}
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="activo"     {{ $evento->estado=='activo'     ? 'selected':'' }}>● Activo</option>
              <option value="cancelado"  {{ $evento->estado=='cancelado'  ? 'selected':'' }}>✕ Cancelado</option>
              <option value="finalizado" {{ $evento->estado=='finalizado' ? 'selected':'' }}>✓ Finalizado</option>
            </select>
          </div>

          {{-- Imagen (OPCIONAL en editar) --}}
          <div class="mb-3">
            <label class="form-label">Imagen actual</label>
            <div class="mb-2">
              <img src="{{ $evento->imagen ? asset('storage/img/'.$evento->imagen) : asset('storage/img/placeholder.png') }}"
                   id="prevEditarEvento{{ $evento->id }}"
                   class="img-preview-evento"
                   onerror="this.src='{{ asset('storage/img/placeholder.png') }}'">
            </div>
            <label class="form-label">Cambiar imagen <span class="text-muted small">(opcional)</span></label>
            <input type="file" name="imagen" class="form-control" accept="image/*"
                   onchange="previewEvento(this,'prevEditarEvento{{ $evento->id }}')">
            <small class="text-muted">Si no seleccionas una nueva imagen, se conserva la actual.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold">💾 Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ MODAL ELIMINAR ══ -->
<div class="modal fade" id="eliminarEvento{{ $evento->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header mh-borrar">
        <h5 class="modal-title">🗑 Eliminar evento</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="eliminar-aviso">
          <p class="mb-1 fw-semibold">⚠️ Esta acción no se puede deshacer.</p>
          <p class="mb-0 text-muted small">
            Estás a punto de eliminar <strong>{{ $evento->titulo }}</strong>.
            Las inscripciones asociadas también se eliminarán.
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <form action="{{ route('admin_eventos.destroy', $evento->id) }}" method="POST" class="d-inline">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">🗑 Sí, eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

// ── Preview imagen
function previewEvento(input, id) {
    const preview = document.getElementById(id);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Validar la hora 
function fechaHoraValida(valor) {
    if (!valor) return false;
    const fecha = new Date(valor);
    if (fecha <= new Date()) return false;
    const hora = fecha.getHours();
    const minutos = fecha.getMinutes();
    // 08:00 hasta 20:00 (20:00 inclusive)
    if (hora < 8) return false;
    if (hora > 20) return false;
    if (hora === 20 && minutos > 0) return false;
    return true;
}

// ── Validación formulario CREAR
document.getElementById('formCrearEvento').addEventListener('submit', function(e) {
    let valido = true;

    const titulo = document.getElementById('crear_titulo');
    const fecha  = document.getElementById('crear_fecha');
    const lugar  = document.getElementById('crear_lugar');
    const desc   = document.getElementById('crear_descripcion');
    const cat    = document.getElementById('crear_categoria');
    const imagen = document.getElementById('crear_imagen');

    // Título
    if (!titulo.value.trim() || titulo.value.trim().length < 3) {
        titulo.classList.add('is-invalid'); valido = false;
    } else { titulo.classList.remove('is-invalid'); titulo.classList.add('is-valid'); }

    // Fecha 
    if (!fechaHoraValida(fecha.value)) {
        fecha.classList.add('is-invalid'); valido = false;
    } else { fecha.classList.remove('is-invalid'); fecha.classList.add('is-valid'); }

    // Lugar
    if (!lugar.value.trim()) {
        lugar.classList.add('is-invalid'); valido = false;
    } else { lugar.classList.remove('is-invalid'); lugar.classList.add('is-valid'); }

    // Descripción
    if (!desc.value.trim()) {
        desc.classList.add('is-invalid'); valido = false;
    } else { desc.classList.remove('is-invalid'); desc.classList.add('is-valid'); }

    // Categoría
    if (!cat.value) {
        cat.classList.add('is-invalid'); valido = false;
    } else { cat.classList.remove('is-invalid'); cat.classList.add('is-valid'); }

    // Imagen (obligatoria al crear)
    if (!imagen.files || imagen.files.length === 0) {
        imagen.classList.add('is-invalid'); valido = false;
    } else { imagen.classList.remove('is-invalid'); imagen.classList.add('is-valid'); }

    if (!valido) {
        e.preventDefault();
        let alerta = document.getElementById('alertaCrearEvento');
        if (!alerta) {
            alerta = document.createElement('div');
            alerta.id = 'alertaCrearEvento';
            alerta.className = 'alert alert-danger mx-3 mt-2';
            alerta.innerHTML = '⚠️ <strong>Completa todos los campos obligatorios antes de guardar.</strong>';
            this.querySelector('.modal-body').prepend(alerta);
        }
    }
});

// ── Validación formularios EDITAR
document.querySelectorAll('[id^="formEditarEvento"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        let valido = true;
        const id = this.id.replace('formEditarEvento', '');

        const titulo = document.getElementById('edit_titulo_' + id);
        const fecha  = document.getElementById('edit_fecha_'  + id);
        const lugar  = document.getElementById('edit_lugar_'  + id);
        const desc   = document.getElementById('edit_desc_'   + id);
        const cat    = document.getElementById('edit_cat_'    + id);

        if (!titulo.value.trim() || titulo.value.trim().length < 3) {
            titulo.classList.add('is-invalid'); valido = false;
        } else { titulo.classList.remove('is-invalid'); titulo.classList.add('is-valid'); }

        // Fecha 
        if (!fechaHoraValida(fecha.value)) {
            fecha.classList.add('is-invalid'); valido = false;
        } else { fecha.classList.remove('is-invalid'); fecha.classList.add('is-valid'); }

        if (!lugar.value.trim()) {
            lugar.classList.add('is-invalid'); valido = false;
        } else { lugar.classList.remove('is-invalid'); lugar.classList.add('is-valid'); }

        if (!desc.value.trim()) {
            desc.classList.add('is-invalid'); valido = false;
        } else { desc.classList.remove('is-invalid'); desc.classList.add('is-valid'); }

        if (!cat.value) {
            cat.classList.add('is-invalid'); valido = false;
        } else { cat.classList.remove('is-invalid'); cat.classList.add('is-valid'); }

        // Imagen en editar 

        if (!valido) {
            e.preventDefault();
            let alerta = this.querySelector('.alerta-editar-evento');
            if (!alerta) {
                alerta = document.createElement('div');
                alerta.className = 'alert alert-danger alerta-editar-evento mx-3 mt-2';
                alerta.innerHTML = '⚠️ <strong>Completa todos los campos obligatorios antes de guardar.</strong>';
                this.querySelector('.modal-body').prepend(alerta);
            }
        }
    });
});

// ── Bloquear fechas pasadas y limitar horario 
document.addEventListener('DOMContentLoaded', function () {
    const now = new Date();
    now.setSeconds(0, 0);
    const min = now.toISOString().slice(0, 16);

    document.querySelectorAll('input[type="datetime-local"]').forEach(el => {
        el.setAttribute('min', min);
        // Aviso al cambiar si la hora está fuera del rango
        el.addEventListener('change', function() {
            const val = new Date(this.value);
            const hora = val.getHours();
            const minutos = val.getMinutes();
            const fueraHorario = hora < 8 || hora > 20 || (hora === 20 && minutos > 0);
            if (fueraHorario) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
});

// ── Búsqueda en tiempo real
const inputBuscar = document.querySelector('.buscador-wrapper input[name="buscar"]');
if (inputBuscar) {
    inputBuscar.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => {
            tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
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