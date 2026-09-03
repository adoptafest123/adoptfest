<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscripción al Evento - Adoptafest</title>
    <link rel="stylesheet" href="{{ asset('css/formulario_eventos.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- NAVBAR  -->
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
                <li class="nav-item ms-3">
                    @if(session('nombre'))
                        <div class="estilo-bienvenida">
                            @if(session('rol') == 'admin')
                                <a href="/admin" class="btn-admin">admin</a>
                            @endif

                            @php
                                $noLeidas = \App\Models\Notificacion::where('user_id', session('id'))
                                    ->where('leida', false)->count();
                            @endphp

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
<div class="hero-form">
    <h1>📋 Inscripción al evento</h1>
    <p>Completa el formulario para participar</p>
</div>

<!-- FORMULARIO -->
<div class="container" style="max-width: 640px;">

    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-3">{{ session('error') }}</div>
    @endif
    @if(session('exito'))
        <div class="alert alert-success rounded-3 mb-3">{{ session('exito') }}</div>
    @endif

    <div class="form-card">

        <!-- Banner del evento -->
        <div class="evento-banner">
            <h5>🎉 {{ $evento->titulo }}</h5>
            <div class="meta">
                <span class="meta-pill">📅 {{ $evento->fecha }}</span>
                <span class="meta-pill">📍 {{ $evento->lugar }}</span>
                @if($evento->categoria)
                    <span class="meta-pill">🏷️ {{ ucfirst($evento->categoria) }}</span>
                @endif
            </div>
        </div>

        <form method="POST" action="/evento/{{ $evento->id }}/formulario_eventos">
            @csrf

            <!-- Separador de sección -->
            <div class="section-divider"><span>Tus datos</span></div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" class="form-control form-control-readonly"
                           value="{{ session('nombre') }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Correo</label>
                    <input type="text" class="form-control form-control-readonly"
                           value="{{ session('email') }}" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="text" name="telefono"
                inputmode="numeric" maxlength="10"
                       class="form-control form-control-custom @error('telefono') is-invalid @enderror"
                       value="{{ old('telefono', session('telefono')) }}"
                       placeholder="Ej: 3001234567">
                @error('telefono')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">¿Por qué quieres participar?</label>
                <textarea name="comentario" rows="3"
                          class="form-control form-control-custom @error('comentario') is-invalid @enderror"
                          placeholder="Cuéntanos un poco sobre ti y tus motivos...">{{ old('comentario') }}</textarea>
                @error('comentario')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(isset($mascotas) && $mascotas->count())
            <div class="mb-4">
                <div class="section-divider">
                    <span>🐾 ¿Te interesa alguna mascota?
                        <small class="text-muted fw-normal">(opcional)</small>
                    </span>
                </div>
                <div class="row g-2">
                    @foreach($mascotas as $m)
                    <div class="col-6">
                        <label class="mascota-label">
                            <input type="checkbox" name="mascotas[]" value="{{ $m->id }}"
                                   {{ in_array($m->id, old('mascotas', [])) ? 'checked' : '' }}>
                            <div class="mascota-card">
                                @if($m->imagen)
                                    <img src="{{ asset('storage/img/'.$m->imagen) }}" class="mascota-foto">
                                @else
                                    <div class="mascota-placeholder">🐾</div>
                                @endif
                                <div>
                                    <div class="fw-semibold small">{{ $m->nombre }}</div>
                                    <span class="badge-disponible">Disponible</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <button type="submit" class="btn-enviar">
                Enviar inscripción 🐾
            </button>

            <a href="/eventos" class="btn-volver">
                ← Volver a eventos
            </a>

        </form>
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
                    <li><a href="/eventos" class="text-white text-decoration-none">Eventos</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>