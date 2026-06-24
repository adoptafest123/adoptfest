<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Adoptafest</title>
    <link rel="stylesheet" href="{{ asset('css/registro.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
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
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="/eventos">Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="/adopcion">Adoptar</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- FORMULARIO -->
<div class="registro-contenedor">

    <div class="registro-logo">
        <img src="{{ asset('storage/img/logo de empresa.png') }}" alt="Logo"
             onerror="this.style.display='none'">
    </div>

    <h3 class="registro-titulo">👋 Iniciar Sesión</h3>
    <p class="registro-sub">Bienvenido de vuelta a Adoptafest</p>

    {{-- ── Errores de validación ── --}}
    @if($errors->any())
        <div class="alert alert-danger text-start rounded-3 py-2 mb-3">
            <ul class="mb-0 ps-3" style="font-size:0.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Mensajes ── --}}
    @if(session('exito'))
        <div class="alert alert-success rounded-3 py-2 mb-3" style="font-size:0.88rem;">
            🎉 {{ session('exito') }}
        </div>
    @endif

    <form action="/login" method="POST" id="formLogin" novalidate>
        @csrf

        {{-- Correo o teléfono --}}
        <div class="campo-grupo">
            <span class="campo-icono">📧</span>
            <input type="text"
                   name="identificador"
                   id="identificador"
                   class="form-control campo-input @error('identificador') is-invalid @enderror"
                   placeholder="Correo o número de teléfono"
                   value="{{ old('identificador') }}"
                   autocomplete="username">
            <div class="invalid-feedback">
                {{ $errors->first('identificador') ?: '⚠️ Ingresa tu correo o teléfono.' }}
            </div>
        </div>

        {{-- Contraseña --}}
        <div class="campo-grupo">
            <span class="campo-icono">🔒</span>
            <input type="password"
                   name="contraseña"
                   id="contraseña"
                   class="form-control campo-input @error('contraseña') is-invalid @enderror"
                   placeholder="Contraseña"
                   autocomplete="current-password">
            <button type="button" class="ojo-btn" onclick="togglePass('contraseña', this)">👁</button>
            <div class="invalid-feedback">⚠️ Ingresa tu contraseña.</div>
        </div>

        <button type="submit" class="btn-registro w-100 mt-2">
            🔑 Ingresar
        </button>
    </form>

    <p class="mt-3 mb-0" style="font-size:0.88rem; color:#6b7280;">
        ¿No tienes cuenta? <a href="/registro" class="link-verde">Regístrate gratis</a>
    </p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

// Validación básica 
document.getElementById('formLogin').addEventListener('submit', function(e) {
    let valido = true;
    const id   = document.getElementById('identificador');
    const pass = document.getElementById('contraseña');

    if (!id.value.trim()) {
        id.classList.add('is-invalid'); valido = false;
    } else {
        id.classList.remove('is-invalid');
    }

    if (!pass.value) {
        pass.classList.add('is-invalid'); valido = false;
    } else {
        pass.classList.remove('is-invalid');
    }

    if (!valido) e.preventDefault();
});

// Auto ocultar alertas
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity 0.5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 3500);

</script>
</body>
</html>