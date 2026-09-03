<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Adoptafest</title>
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

    <h3 class="registro-titulo">🐾 Crear Cuenta</h3>
    <p class="registro-sub">Únete a Adoptafest y ayuda a cambiar vidas</p>

    {{-- ── Errores de validación --}}
    @if($errors->any())
        <div class="alert alert-danger text-start rounded-3 py-2 mb-3">
            <ul class="mb-0 ps-3" style="font-size:0.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Mensajes de sesión ── --}}
    @if(session('exito'))
        <div class="alert alert-success rounded-3 py-2 mb-3" style="font-size:0.88rem;">
            🎉 {{ session('exito') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3 py-2 mb-3" style="font-size:0.88rem;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <form action="/registro" method="POST" id="formRegistro" novalidate>
        @csrf

        {{-- Nombre --}}
        <div class="campo-grupo">
            <span class="campo-icono">👤</span>
            <input type="text"
                   name="nombre"
                   id="nombre"
                   class="form-control campo-input @error('nombre') is-invalid @enderror"
                   placeholder="Nombre completo"
                   value="{{ old('nombre') }}"
                   maxlength="100">
            <div class="invalid-feedback">⚠️ El nombre es obligatorio (mínimo 2 caracteres).</div>
        </div>

        {{-- Correo --}}
        <div class="campo-grupo">
            <span class="campo-icono">📧</span>
            <input type="email"
                   name="correo"
                   id="correo"
                   class="form-control campo-input @error('correo') is-invalid @enderror"
                   placeholder="Correo electrónico"
                   value="{{ old('correo') }}"
                   maxlength="255">
            <div class="invalid-feedback">⚠️ Ingresa un correo válido.</div>
        </div>

        {{-- Teléfono --}}
        <div class="campo-grupo">
            <span class="campo-icono">📱</span>
            <input type="tel"
                   name="telefono"
                   id="telefono"
                   class="form-control campo-input @error('telefono') is-invalid @enderror"
                   placeholder="Teléfono (ej: 3001234567)"
                   value="{{ old('telefono') }}"
                   maxlength="20">
            <div class="invalid-feedback">⚠️ Ingresa un número válido (mínimo 7 dígitos).</div>
        </div>

        {{-- Contraseña --}}
        <div class="campo-grupo">
            <span class="campo-icono">🔒</span>
            <input type="password"
                   name="contraseña"
                   id="contraseña"
                   class="form-control campo-input @error('contraseña') is-invalid @enderror"
                   placeholder="Contraseña (mínimo 6 caracteres)">
            <button type="button" class="ojo-btn" onclick="togglePass('contraseña', this)">👁</button>
            <div class="invalid-feedback">⚠️ La contraseña debe tener al menos 6 caracteres.</div>
        </div>

        {{-- Confirmar contraseña --}}
        <div class="campo-grupo">
            <span class="campo-icono">🔒</span>
            <input type="password"
                   name="contraseña_confirm"
                   id="contraseña_confirm"
                   class="form-control campo-input"
                   placeholder="Confirmar contraseña">
            <button type="button" class="ojo-btn" onclick="togglePass('contraseña_confirm', this)">👁</button>
            <div class="invalid-feedback">⚠️ Las contraseñas no coinciden.</div>
        </div>

        {{-- Fuerza de contraseña --}}
        <div class="fuerza-wrapper" id="fuerzaWrapper" style="display:none;">
            <div class="fuerza-barra">
                <div class="fuerza-fill" id="fuerzaFill"></div>
            </div>
            <small class="fuerza-texto" id="fuerzaTexto"></small>
        </div>

        <button type="submit" class="btn-registro w-100 mt-3">
            🐾 Crear mi cuenta
        </button>
    </form>

    <p class="mt-3 mb-0" style="font-size:0.88rem; color:#6b7280;">
        ¿Ya tienes cuenta? <a href="/login" class="link-verde">Inicia sesión</a>
    </p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

// ── Mostrar/ocultar contraseña
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

// ── Fuerza de contraseña 
document.getElementById('contraseña').addEventListener('input', function () {
    const val   = this.value;
    const wrap  = document.getElementById('fuerzaWrapper');
    const fill  = document.getElementById('fuerzaFill');
    const texto = document.getElementById('fuerzaTexto');

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const niveles = [
        { label: 'Muy débil', color: '#ef4444', w: '20%' },
        { label: 'Débil',     color: '#f97316', w: '40%' },
        { label: 'Regular',   color: '#eab308', w: '60%' },
        { label: 'Fuerte',    color: '#22c55e', w: '80%' },
        { label: 'Muy fuerte',color: '#16a34a', w: '100%'},
    ];
    const n = niveles[Math.min(score, 4)];
    fill.style.width      = n.w;
    fill.style.background = n.color;
    texto.textContent     = n.label;
    texto.style.color     = n.color;
});

// ── Validación client-side
document.getElementById('formRegistro').addEventListener('submit', function (e) {
    let valido = true;

    const nombre   = document.getElementById('nombre');
    const correo   = document.getElementById('correo');
    const telefono = document.getElementById('telefono');
    const pass     = document.getElementById('contraseña');
    const passConf = document.getElementById('contraseña_confirm');

    // Nombre
    if (!nombre.value.trim() || nombre.value.trim().length < 2) {
        nombre.classList.add('is-invalid'); valido = false;
    } else {
        nombre.classList.remove('is-invalid'); nombre.classList.add('is-valid');
    }

    // Correo
    const emailReg = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailReg.test(correo.value.trim())) {
        correo.classList.add('is-invalid'); valido = false;
    } else {
        correo.classList.remove('is-invalid'); correo.classList.add('is-valid');
    }

    // Teléfono 
    if (telefono.value.trim() && !/^\d{7,20}$/.test(telefono.value.trim())) {
        telefono.classList.add('is-invalid'); valido = false;
    } else {
        telefono.classList.remove('is-invalid');
        if (telefono.value.trim()) telefono.classList.add('is-valid');
    }

    // Contraseña
    if (pass.value.length < 6) {
        pass.classList.add('is-invalid'); valido = false;
    } else {
        pass.classList.remove('is-invalid'); pass.classList.add('is-valid');
    }

    // Confirmar contraseña
    if (passConf.value !== pass.value || !passConf.value) {
        passConf.classList.add('is-invalid'); valido = false;
    } else {
        passConf.classList.remove('is-invalid'); passConf.classList.add('is-valid');
    }

    if (!valido) e.preventDefault();
});

// ── Auto ocultar alertas de sesión 
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