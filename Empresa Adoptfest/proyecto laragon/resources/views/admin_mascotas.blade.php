<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Adoptafest - Admin Mascotas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/admin_mascotas.css') }}">
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
    <h1>🐾 Administrar Mascotas</h1>
    <p>Gestiona las mascotas disponibles para adopción</p>
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
      Total: <strong>{{ $mascotas->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#16a34a;"></div>
      Disponibles: <strong>{{ $mascotas->where('estado','disponible')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#d97706;"></div>
      En proceso: <strong>{{ $mascotas->where('estado','proceso')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#2563eb;"></div>
      En evento: <strong>{{ $mascotas->where('estado','evento')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#7c3aed;"></div>
      Perros: <strong>{{ $mascotas->where('tipo','perro')->count() }}</strong>
    </div>
    <div class="stat-pill">
      <div class="dot" style="background:#db2777;"></div>
       Gatos: <strong>{{ $mascotas->where('tipo','gato')->count() }}</strong>
    </div>
  </div>

  <!-- BUSCADOR -->
  <div class="buscador-wrapper">
    <form method="GET" action="/admin_mascotas" id="formBuscar">
      <div class="row g-3 align-items-end">

        <div class="col-lg-4 col-md-6">
          <label class="form-label fw-semibold small text-muted">🔍 Buscar por nombre o código</label>
          <input type="text" name="buscar" class="form-control"
                 placeholder="Ej: Luna o ABC-123"
                 value="{{ request('buscar') }}">
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label fw-semibold small text-muted">🐾 Tipo</label>
          <select name="tipo" class="form-select">
            <option value="">Todos</option>
            <option value="perro" {{ request('tipo')=='perro' ? 'selected':'' }}> Perro</option>
            <option value="gato"  {{ request('tipo')=='gato'  ? 'selected':'' }}> Gato</option>
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <label class="form-label fw-semibold small text-muted">📌 Estado</label>
          <select name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="disponible" {{ request('estado')=='disponible' ? 'selected':'' }}>Disponible</option>
            <option value="proceso"    {{ request('estado')=='proceso'    ? 'selected':'' }}>En proceso</option>
            <option value="evento"     {{ request('estado')=='evento'     ? 'selected':'' }}>En evento</option>
          </select>
        </div>

        <div class="col-lg-4 d-flex gap-2 align-items-end">
          <button type="submit" class="btn-buscar flex-fill">🔍 Buscar</button>
          @if(request('buscar') || request('estado') || request('tipo'))
            <a href="/admin_mascotas" class="btn-limpiar">✕ Limpiar</a>
          @endif
          <button type="button" class="btn-crear ms-auto"
                  data-bs-toggle="modal" data-bs-target="#crearMascota">
            ➕ Registrar
          </button>
        </div>

      </div>
      @if(request('buscar') || request('estado') || request('tipo'))
        <div class="mt-2 small text-muted">
          Resultados para:
          @if(request('buscar')) <span class="badge bg-success">{{ request('buscar') }}</span> @endif
          @if(request('tipo'))   <span class="badge bg-purple" style="background:#7c3aed;">{{ ucfirst(request('tipo')) }}</span> @endif
          @if(request('estado')) <span class="badge bg-dark">{{ ucfirst(request('estado')) }}</span> @endif
        </div>
      @endif
    </form>
  </div>

  <!-- TABLA -->
  @if($mascotas->count() > 0)
  <div class="table-responsive">
    <table class="table tabla-mascotas align-middle">
      <thead>
        <tr>
          <th>Código</th>
          <th>Foto</th>
          <th>Nombre</th>
          <th>Tipo</th>
          <th>Edad</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($mascotas as $mascota)
        <tr>
          <td><span class="placa-badge">{{ $mascota->codigo ?? '—' }}</span></td>
          <td>
            <img src="{{ asset('storage/img/'.($mascota->imagen ?? 'placeholder.png')) }}"
                 class="foto-mascota"
                 onerror="this.src='{{ asset('img/placeholder.png') }}'">
          </td>
          <td class="fw-semibold">{{ $mascota->nombre }}</td>
          <td>
            @if(($mascota->tipo ?? 'perro') == 'perro')
              <span class="badge rounded-pill" style="background:#7c3aed;"> Perro</span>
            @else
              <span class="badge rounded-pill" style="background:#db2777;"> Gato</span>
            @endif
          </td>
          <td>{{ $mascota->edad }} años</td>
          <td>
            @if($mascota->estado == 'disponible')
              <span class="badge-disponible">● Disponible</span>
            @elseif($mascota->estado == 'proceso')
              <span class="badge-proceso">⏳ En proceso</span>
            @else
              <span class="badge-evento">🎉 En evento</span>
            @endif
          </td>
          <td>
            <div class="d-flex gap-2 flex-wrap">
              <button class="btn-ver2"  data-bs-toggle="modal" data-bs-target="#verMascota{{ $mascota->id }}">👁 Ver</button>
              <button class="btn-edit2" data-bs-toggle="modal" data-bs-target="#editarMascota{{ $mascota->id }}">✏️ Editar</button>
              <button class="btn-del2"  data-bs-toggle="modal" data-bs-target="#eliminarMascota{{ $mascota->id }}">🗑 Eliminar</button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="sin-resultados">
    <p style="font-size:3rem;">🐾</p>
    <h5 class="fw-bold text-muted">Sin resultados</h5>
    <p class="text-muted">No se encontraron mascotas con los filtros aplicados.</p>
    <a href="/admin_mascotas" class="btn btn-success rounded-pill px-4">Ver todas</a>
  </div>
  @endif

</div>

{{-- MODALES --}}

<!-- ══ MODAL CREAR ══ -->
<div class="modal fade" id="crearMascota" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form action="/admin_mascotas" method="POST" enctype="multipart/form-data"
            id="formCrear" novalidate>
        @csrf
        <div class="modal-header mh-crear">
          <h5 class="modal-title">➕ Registrar nueva mascota</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="alert alert-light border small mb-3">
            🏷️ El <strong>código</strong> se genera automáticamente al guardar.
          </div>

          {{-- Nombre --}}
          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="c_nombre" class="form-control"
                   placeholder="Ej: Luna" minlength="2" maxlength="100">
            <div class="invalid-feedback">⚠️ El nombre es obligatorio (mínimo 2 caracteres).</div>
          </div>

          {{-- Tipo --}}
          <div class="mb-3">
            <label class="form-label">Tipo de animal <span class="text-danger">*</span></label>
            <select name="tipo" id="c_tipo" class="form-select">
              <option value="">-- Selecciona --</option>
              <option value="perro"> Perro</option>
              <option value="gato"> Gato</option>
            </select>
            <div class="invalid-feedback">⚠️ Debes seleccionar el tipo de animal.</div>
          </div>

          {{-- Edad --}}
          <div class="mb-3">
            <label class="form-label">Edad (años) <span class="text-danger">*</span></label>
            <div class="edad-wrapper">
              <input type="number" name="edad" id="c_edad" class="form-control"
                     placeholder="1" min="1" max="5">
              <span class="edad-suffix">años</span>
            </div>
            <small class="text-muted">Entre 1 y 5 años.</small>
            <div class="invalid-feedback">⚠️ La edad debe estar entre 1 y 5 años.</div>
          </div>

          {{-- Descripción --}}
          <div class="mb-3">
            <label class="form-label">Descripción breve <span class="text-danger">*</span></label>
            <input type="text" name="descripcion" id="c_descripcion" class="form-control"
                   placeholder="Ej: Perro amigable y juguetón" maxlength="255">
            <div class="invalid-feedback">⚠️ La descripción es obligatoria.</div>
          </div>

          {{-- Historia (AHORA OBLIGATORIA) --}}
          <div class="mb-3">
            <label class="form-label">Historia <span class="text-danger">*</span></label>
            <textarea name="historia" id="c_historia" class="form-control" rows="3"
                      placeholder="Cuéntanos la historia de la mascota..." maxlength="1000"></textarea>
            <div class="invalid-feedback">⚠️ La historia es obligatoria.</div>
          </div>

          {{-- Imagen (AHORA OBLIGATORIA) --}}
          <div class="mb-3">
            <label class="form-label">Imagen <span class="text-danger">*</span></label>
            <input type="file" name="imagen" id="c_imagen" class="form-control" accept="image/*"
                   onchange="previewImg(this,'prevCrear')">
            <div class="invalid-feedback">⚠️ Debes seleccionar una imagen.</div>
            <img id="prevCrear" class="img-preview mt-2" style="display:none;">
          </div>

          <input type="hidden" name="estado" value="disponible">
          <div class="alert alert-success border-0 small py-2">
            ✅ El estado se fija en <strong>Disponible</strong> al crear.
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                  data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">
            💾 Registrar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($mascotas as $mascota)

<!-- ══ MODAL VER ══ -->
<div class="modal fade" id="verMascota{{ $mascota->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header mh-ver">
        <h5 class="modal-title">👁 Información de la mascota</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <img src="{{ asset('storage/img/'.($mascota->imagen ?? 'placeholder.png')) }}"
               class="img-preview mx-auto" style="width:100px;height:100px;"
               onerror="this.src='{{ asset('img/placeholder.png') }}'">
          <div class="fw-bold fs-5 mt-2">{{ $mascota->nombre }}</div>
          <span class="placa-badge">{{ $mascota->codigo ?? '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">🏷️ Código</span>
          <span class="info-valor"><code>{{ $mascota->codigo ?? '—' }}</code></span>
        </div>
        <div class="info-row">
          <span class="info-label">🐾 Tipo</span>
          <span class="info-valor">{{ ucfirst($mascota->tipo ?? 'perro') }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">🎂 Edad</span>
          <span class="info-valor">{{ $mascota->edad }} años</span>
        </div>
        <div class="info-row">
          <span class="info-label">📝 Descripción</span>
          <span class="info-valor">{{ $mascota->descripcion ?? '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">📖 Historia</span>
          <span class="info-valor">{{ $mascota->historia ?? '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">📌 Estado</span>
          <span class="info-valor">
            @if($mascota->estado=='disponible') <span class="badge-disponible">● Disponible</span>
            @elseif($mascota->estado=='proceso') <span class="badge-proceso">⏳ En proceso</span>
            @else <span class="badge-evento">🎉 En evento</span>
            @endif
          </span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL EDITAR ══ -->
<div class="modal fade" id="editarMascota{{ $mascota->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <form action="/admin_mascotas/{{ $mascota->id }}" method="POST"
            enctype="multipart/form-data" id="formEditar{{ $mascota->id }}" novalidate>
        @csrf
        @method('PUT')
        <div class="modal-header mh-editar">
          <h5 class="modal-title">✏️ Editar — {{ $mascota->nombre }}</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control bg-light"
                   value="{{ $mascota->codigo ?? '—' }}" readonly>
            <small class="text-muted">El código no se puede cambiar.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="e_nombre_{{ $mascota->id }}"
                   class="form-control" value="{{ $mascota->nombre }}"
                   minlength="2" maxlength="100">
            <div class="invalid-feedback">⚠️ El nombre es obligatorio.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Tipo de animal <span class="text-danger">*</span></label>
            <select name="tipo" id="e_tipo_{{ $mascota->id }}" class="form-select">
              <option value="">-- Selecciona --</option>
              <option value="perro" {{ ($mascota->tipo ?? 'perro')=='perro' ? 'selected':'' }}> Perro</option>
              <option value="gato"  {{ ($mascota->tipo ?? '')=='gato'  ? 'selected':'' }}> Gato</option>
            </select>
            <div class="invalid-feedback">⚠️ Selecciona el tipo de animal.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Edad (años) <span class="text-danger">*</span></label>
            <div class="edad-wrapper">
              <input type="number" name="edad" id="e_edad_{{ $mascota->id }}"
                     class="form-control" value="{{ (int)$mascota->edad }}"
                     min="1" max="5">
              <span class="edad-suffix">años</span>
            </div>
            <small class="text-muted">Entre 1 y 5 años.</small>
            <div class="invalid-feedback">⚠️ La edad debe estar entre 1 y 5 años.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <input type="text" name="descripcion" id="e_desc_{{ $mascota->id }}"
                   class="form-control" value="{{ $mascota->descripcion }}" maxlength="255">
            <div class="invalid-feedback">⚠️ La descripción es obligatoria.</div>
          </div>

          {{-- Historia (AHORA OBLIGATORIA en editar) --}}
          <div class="mb-3">
            <label class="form-label">Historia <span class="text-danger">*</span></label>
            <textarea name="historia" id="e_historia_{{ $mascota->id }}" class="form-control" rows="3"
                      maxlength="1000">{{ $mascota->historia }}</textarea>
            <div class="invalid-feedback">⚠️ La historia es obligatoria.</div>
          </div>

          {{-- Imagen (OPCIONAL en editar, ya tiene una guardada) --}}
          <div class="mb-3">
            <label class="form-label">Imagen actual</label>
            <div class="mb-2">
              <img src="{{ asset('storage/img/'.($mascota->imagen ?? 'placeholder.png')) }}"
                   id="prevEditar{{ $mascota->id }}" class="img-preview"
                   onerror="this.src='{{ asset('img/placeholder.png') }}'">
            </div>
            <label class="form-label">Cambiar imagen <span class="text-muted small">(opcional)</span></label>
            <input type="file" name="imagen" class="form-control" accept="image/*"
                   onchange="previewImg(this,'prevEditar{{ $mascota->id }}')">
            <small class="text-muted">Si no seleccionas una nueva imagen, se conserva la actual.</small>
          </div>

          <div class="mb-1">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="disponible" {{ $mascota->estado=='disponible' ? 'selected':'' }}>● Disponible</option>
              <option value="proceso"    {{ $mascota->estado=='proceso'    ? 'selected':'' }}>⏳ En proceso</option>
              <option value="evento"     {{ $mascota->estado=='evento'     ? 'selected':'' }}>🎉 En evento</option>
            </select>
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
<div class="modal fade" id="eliminarMascota{{ $mascota->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header mh-borrar">
        <h5 class="modal-title">🗑 Eliminar mascota</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="eliminar-aviso">
          <p class="mb-1 fw-semibold">⚠️ Esta acción no se puede deshacer.</p>
          <p class="mb-0 text-muted small">
            Estás a punto de eliminar a <strong>{{ $mascota->nombre }}</strong>
            <span class="placa-badge ms-1">{{ $mascota->codigo ?? '' }}</span>
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                data-bs-dismiss="modal">Cancelar</button>
        <form action="/admin_mascotas/{{ $mascota->id }}" method="POST" class="d-inline">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">
            🗑 Sí, eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Preview imagen
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Validación formulario CREAR
document.getElementById('formCrear').addEventListener('submit', function(e) {
    let valido = true;

    const nombre   = document.getElementById('c_nombre');
    const tipo     = document.getElementById('c_tipo');
    const edad     = document.getElementById('c_edad');
    const desc     = document.getElementById('c_descripcion');
    const historia = document.getElementById('c_historia');
    const imagen   = document.getElementById('c_imagen');

    // Nombre
    if (!nombre.value.trim() || nombre.value.trim().length < 2) {
        nombre.classList.add('is-invalid');
        valido = false;
    } else { nombre.classList.remove('is-invalid'); nombre.classList.add('is-valid'); }

    // Tipo
    if (!tipo.value) {
        tipo.classList.add('is-invalid');
        valido = false;
    } else { tipo.classList.remove('is-invalid'); tipo.classList.add('is-valid'); }

    // Edad
    const edadVal = parseInt(edad.value);
    if (!edad.value || edadVal < 1 || edadVal > 5) {
        edad.classList.add('is-invalid');
        valido = false;
    } else { edad.classList.remove('is-invalid'); edad.classList.add('is-valid'); }

    // Descripción
    if (!desc.value.trim()) {
        desc.classList.add('is-invalid');
        valido = false;
    } else { desc.classList.remove('is-invalid'); desc.classList.add('is-valid'); }

    // Historia 
    if (!historia.value.trim()) {
        historia.classList.add('is-invalid');
        valido = false;
    } else { historia.classList.remove('is-invalid'); historia.classList.add('is-valid'); }

    // Imagen 
    if (!imagen.files || imagen.files.length === 0) {
        imagen.classList.add('is-invalid');
        valido = false;
    } else { imagen.classList.remove('is-invalid'); imagen.classList.add('is-valid'); }

    if (!valido) {
        e.preventDefault();
        let alerta = document.getElementById('alertaCrear');
        if (!alerta) {
            alerta = document.createElement('div');
            alerta.id = 'alertaCrear';
            alerta.className = 'alert alert-danger mx-3 mt-2';
            alerta.innerHTML = '⚠️ <strong>Completa todos los campos obligatorios antes de guardar.</strong>';
            this.querySelector('.modal-body').prepend(alerta);
        }
    }
});

// ── Validación formularios EDITAR
document.querySelectorAll('[id^="formEditar"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        let valido = true;
        const id = this.id.replace('formEditar','');

        const nombre   = document.getElementById('e_nombre_'  + id);
        const tipo     = document.getElementById('e_tipo_'    + id);
        const edad     = document.getElementById('e_edad_'    + id);
        const desc     = document.getElementById('e_desc_'    + id);
        const historia = document.getElementById('e_historia_' + id);

        if (!nombre.value.trim() || nombre.value.trim().length < 2) {
            nombre.classList.add('is-invalid'); valido = false;
        } else { nombre.classList.remove('is-invalid'); nombre.classList.add('is-valid'); }

        if (!tipo.value) {
            tipo.classList.add('is-invalid'); valido = false;
        } else { tipo.classList.remove('is-invalid'); tipo.classList.add('is-valid'); }

        const edadVal = parseInt(edad.value);
        if (!edad.value || edadVal < 1 || edadVal > 5) {
            edad.classList.add('is-invalid'); valido = false;
        } else { edad.classList.remove('is-invalid'); edad.classList.add('is-valid'); }

        if (!desc.value.trim()) {
            desc.classList.add('is-invalid'); valido = false;
        } else { desc.classList.remove('is-invalid'); desc.classList.add('is-valid'); }

        // Historia 
        if (!historia.value.trim()) {
            historia.classList.add('is-invalid'); valido = false;
        } else { historia.classList.remove('is-invalid'); historia.classList.add('is-valid'); }

        // Imagen en editar 

        if (!valido) {
            e.preventDefault();
            let alerta = this.querySelector('.alerta-editar');
            if (!alerta) {
                alerta = document.createElement('div');
                alerta.className = 'alert alert-danger alerta-editar mx-3 mt-2';
                alerta.innerHTML = '⚠️ <strong>Completa todos los campos obligatorios antes de guardar.</strong>';
                this.querySelector('.modal-body').prepend(alerta);
            }
        }
    });
});

// ── Búsqueda en tiempo real en tabla
document.querySelector('input[name="buscar"]').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});

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