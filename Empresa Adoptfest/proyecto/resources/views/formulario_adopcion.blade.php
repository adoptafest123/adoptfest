<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Adopción - Adoptafest</title>
    <link rel="stylesheet" href="{{ asset('css/formulario_adopcion.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                            <button class="btn-perfil" data-bs-toggle="modal" data-bs-target="#modalPerfil" style="position:relative;">
                                @if(session('foto'))
                                    <img src="{{ asset('storage/img/perfiles/' . session('foto')) }}" class="avatar-perfil" style="object-fit:cover;" alt="foto">
                                @else
                                    <div class="avatar-perfil">{{ strtoupper(substr(session('nombre'), 0, 1)) }}</div>
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
    <h1>🐾 Solicitud de Adopción</h1>
    <p>Completa el formulario con honestidad — nos ayuda a encontrar el mejor hogar para cada mascota</p>
</div>

<!-- FORMULARIO -->
<div class="container" style="max-width: 640px;">

    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-4">❌ {{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <strong>⚠️ Hay campos incompletos o incorrectos:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Alerta JS (se llena dinámicamente) --}}
    <div id="alerta-campos" class="alert alert-danger rounded-3 mb-4" style="display:none;">
        <strong>⚠️ Hay campos incompletos o incorrectos:</strong>
        <ul id="lista-errores" class="mb-0 mt-1"></ul>
    </div>

    <!-- RESUMEN MASCOTA -->
    <div class="mascota-resumen">
        <img src="{{ asset('storage/img/' . ($mascota->imagen ?? 'placeholder.png')) }}"
             alt="{{ $mascota->nombre }}"
             onerror="this.src='{{ asset('img/placeholder.png') }}'">
        <div>
            <h4>
                {{ $mascota->nombre }}
                <span class="badge-disponible">✅ Disponible</span>
            </h4>
            <small>🎂 {{ $mascota->edad }} año(s)</small>
            <p class="mb-0 mt-1" style="font-size:.88rem; color:#4b5563;">{{ $mascota->descripcion }}</p>
        </div>
    </div>

    <!-- BARRA DE PROGRESO -->
    <div class="progreso-wrapper">
        <div class="progreso-header">
            <span>Progreso del formulario</span>
            <span id="progreso-texto">0% completado</span>
        </div>
        <div class="progreso-bar">
            <div class="progreso-fill" id="progreso" style="width:0%"></div>
        </div>
        <div class="paso-dots">
            <div class="paso-dot activo"></div>
            <div class="paso-dot"></div>
            <div class="paso-dot"></div>
            <div class="paso-dot"></div>
            <div class="paso-dot"></div>
        </div>
    </div>

    <!-- FORMULARIO -->
    <form action="/adopcion/{{ $mascota->id }}/solicitud" method="POST" id="formAdopcion" novalidate>
        @csrf

        {{-- ══ SECCIÓN 1: DATOS PERSONALES ══ --}}
        <div class="seccion">
            <div class="seccion-titulo">
                <div class="seccion-num">1</div>
                👤 Datos Personales
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_completo" id="nombre_completo" class="form-control"
                           value="{{ old('nombre_completo', session('nombre')) }}"
                           placeholder="Ej: María García López">
                    <div class="invalid-feedback">⚠️ El nombre completo es obligatorio.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cédula de ciudadanía <span class="text-danger">*</span></label>
                    <input type="text" name="cedula" id="cedula" class="form-control"
                           value="{{ old('cedula') }}"
                           placeholder="Ej: 1234567890"
                           inputmode="numeric" maxlength="10"
                           oninput="soloNumeros(this); validarCedula(this)">
                    <div class="invalid-feedback" id="err-cedula">⚠️ La cédula debe tener entre 6 y 10 dígitos.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono / Celular <span class="text-danger">*</span></label>
                    <input type="text" name="telefono" id="telefono" class="form-control"
                           value="{{ old('telefono', session('telefono')) }}"
                           placeholder="Ej: 3001234567"
                           inputmode="numeric" maxlength="10"
                           oninput="soloNumeros(this); validarTelefono(this)">
                    <div class="invalid-feedback" id="err-telefono">⚠️ El teléfono debe tener exactamente 10 dígitos.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" value="{{ session('email') }}" readonly>
                    <div class="form-hint">Tu correo registrado</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Dirección de residencia <span class="text-danger">*</span></label>
                    <input type="text" name="direccion" id="direccion" class="form-control"
                           value="{{ old('direccion') }}"
                           placeholder="Ej: Cra 15 #80-45, Apto 302">
                    <div class="invalid-feedback">⚠️ La dirección es obligatoria.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                    <input type="text" name="ciudad" id="ciudad" class="form-control"
                           value="{{ old('ciudad', 'Bogotá') }}">
                    <div class="invalid-feedback">⚠️ La ciudad es obligatoria.</div>
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 2: VIVIENDA ══ --}}
        <div class="seccion">
            <div class="seccion-titulo">
                <div class="seccion-num">2</div>
                🏠 Tu Vivienda
            </div>

            <label class="form-label mb-2">Tipo de vivienda <span class="text-danger">*</span></label>
            <div class="radio-group" id="grupo-vivienda">
                <label class="radio-card" id="rc-casa">
                    <input type="radio" name="tipo_vivienda" value="casa" onchange="seleccionarRadio('casa')">
                    <span class="icono">🏡</span>
                    <span class="texto">Casa</span>
                </label>
                <label class="radio-card" id="rc-apartamento">
                    <input type="radio" name="tipo_vivienda" value="apartamento" onchange="seleccionarRadio('apartamento')">
                    <span class="icono">🏢</span>
                    <span class="texto">Apartamento</span>
                </label>
                <label class="radio-card" id="rc-finca">
                    <input type="radio" name="tipo_vivienda" value="finca" onchange="seleccionarRadio('finca')">
                    <span class="icono">🌾</span>
                    <span class="texto">Finca</span>
                </label>
            </div>
            {{-- Error vivienda --}}
            <div class="invalid-feedback" id="err-vivienda" style="display:none;">⚠️ Selecciona el tipo de vivienda.</div>

            <div class="check-grid mb-3 mt-3">
                <div class="check-card">
                    <input type="checkbox" name="tiene_patio" id="tiene_patio" value="1">
                    <label for="tiene_patio">🌿 Tiene patio o jardín</label>
                </div>
                <div class="check-card">
                    <input type="checkbox" name="es_propia" id="es_propia" value="1">
                    <label for="es_propia">🔑 Vivienda propia</label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">¿Cuántas personas viven contigo? <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="personas_en_casa" id="personas_en_casa" class="form-control"
                               min="1" max="8" value="{{ old('personas_en_casa', 1) }}"
                               oninput="limitarRango(this, 1, 8)">
                        <span class="input-group-text">personas</span>
                    </div>
                    <div class="invalid-feedback">⚠️ Este campo es obligatorio.</div>
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 3: CONVIVENCIA ══ --}}
        <div class="seccion">
            <div class="seccion-titulo">
                <div class="seccion-num">3</div>
                👨‍👩‍👧 Convivencia en el Hogar
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">¿Hay niños en casa? <span class="text-danger">*</span></label>
                <div class="d-flex gap-3 mt-2" id="grupo-ninos">
                    <label class="radio-card" id="rc-ninos-si" style="flex:1;">
                        <input type="radio" name="tiene_ninos" value="1" id="ninos_si"
                               onchange="toggleNinos(true)">
                        <span class="icono">✅</span>
                        <span class="texto">Sí</span>
                    </label>
                    <label class="radio-card" id="rc-ninos-no" style="flex:1;">
                        <input type="radio" name="tiene_ninos" value="0" id="ninos_no"
                               onchange="toggleNinos(false)">
                        <span class="icono">❌</span>
                        <span class="texto">No</span>
                    </label>
                </div>
                {{-- Error niños --}}
                <div class="invalid-feedback" id="err-ninos" style="display:none;">⚠️ Indica si hay niños en casa.</div>
            </div>

            <div class="mt-3">
                <label class="form-label fw-semibold">¿Tienes otras mascotas en casa?</label>
                <div class="check-grid mt-2">

                    <div class="check-card" style="flex-direction:column; gap:10px; padding:14px 12px; text-align:center;">
                        <div style="font-size:1.8rem;">🐶</div>
                        <div style="font-size:.85rem; color:#6b7280;">Perros</div>
                        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
                            <button type="button" onclick="cambiarCantidad('perros', -1)" class="btn-contador">−</button>
                            <span id="val-perros" style="font-size:1.1rem; font-weight:600; min-width:20px;">0</span>
                            <button type="button" onclick="cambiarCantidad('perros', 1)" class="btn-contador">+</button>
                        </div>
                    </div>

                    <div class="check-card" style="flex-direction:column; gap:10px; padding:14px 12px; text-align:center;">
                        <div style="font-size:1.8rem;">🐱</div>
                        <div style="font-size:.85rem; color:#6b7280;">Gatos</div>
                        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
                            <button type="button" onclick="cambiarCantidad('gatos', -1)" class="btn-contador">−</button>
                            <span id="val-gatos" style="font-size:1.1rem; font-weight:600; min-width:20px;">0</span>
                            <button type="button" onclick="cambiarCantidad('gatos', 1)" class="btn-contador">+</button>
                        </div>
                    </div>

                </div>

                <input type="hidden" name="num_perros" id="inp-perros" value="0">
                <input type="hidden" name="num_gatos"  id="inp-gatos"  value="0">
                <input type="hidden" name="tiene_otros_animales" id="inp-tiene-animales" value="0">
            </div>
        </div>

        {{-- ══ SECCIÓN 4: EXPERIENCIN ══ --}}
        <div class="seccion">
            <div class="seccion-titulo">
                <div class="seccion-num">4</div>
                🎓 Experiencia con Mascotas <span class="badge bg-secondary ms-2" style="font-size:.7rem;">Opcional</span>
            </div>

            <div class="check-grid mb-3">
                <div class="check-card">
                    <input type="checkbox" name="tiene_experiencia" id="tiene_experiencia" value="1"
                           onchange="toggleCampo('tiene_experiencia','campo-experiencia')">
                    <label for="tiene_experiencia">✅ Tengo experiencia previa con mascotas</label>
                </div>
            </div>

            <div id="campo-experiencia" class="campo-extra" style="display:none;">
                <label class="form-label">Cuéntanos tu experiencia</label>
                <div class="textarea-wrapper">
                    <textarea name="descripcion_experiencia" class="form-control" rows="3"
                              maxlength="500"
                              placeholder="Ej: Tuve un labrador por 8 años, lo vacuné, esterilicé y entrené..."
                              oninput="contarChars(this, 'cnt-exp')">{{ old('descripcion_experiencia') }}</textarea>
                    <span class="char-count" id="cnt-exp">0/500</span>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">¿Cuántas horas al día estaría sola la mascota? <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="horas_sola_mascota" id="horas_sola_mascota" class="form-control"
                               min="0" max="24" value="{{ old('horas_sola_mascota', 0) }}"
                               oninput="limitarRango(this, 0, 24)">
                        <span class="input-group-text">horas</span>
                    </div>
                    <div class="invalid-feedback">⚠️ Este campo es obligatorio.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">¿Quién cuidaría la mascota en tu ausencia?</label>
                    <input type="text" name="quien_cuida_ausencia" class="form-control"
                           value="{{ old('quien_cuida_ausencia') }}"
                           placeholder="Ej: Mi pareja, un familiar, un vecino">
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 5: MOTIVACIÓN ══ --}}
        <div class="seccion">
            <div class="seccion-titulo">
                <div class="seccion-num">5</div>
                💚 Tu Motivación
            </div>

            {{-- Motivo --}}
            <div class="mb-4">
                <label class="form-label">¿Por qué quieres adoptar a {{ $mascota->nombre }}? <span class="text-danger">*</span></label>
                <div class="textarea-wrapper">
                    <textarea name="motivo_adopcion" id="motivo_adopcion" class="form-control" rows="5"
                              minlength="20" maxlength="1000"
                              placeholder="Cuéntanos qué te motivó a querer adoptar esta mascota..."
                              oninput="contarChars(this, 'cnt-motivo')">{{ old('motivo_adopcion') }}</textarea>
                    <span class="char-count" id="cnt-motivo">0/1000</span>
                </div>
                <div class="invalid-feedback" id="err-motivo">⚠️ Cuéntanos tu motivación (mínimo 20 caracteres).</div>
            </div>

            {{-- Compromiso  --}}
            <div class="mb-3">
                <label class="form-label">Compromiso de responsabilidad <span class="text-danger">*</span></label>
                <div class="textarea-wrapper">
                    <textarea name="compromiso" id="compromiso" class="form-control" rows="3"
                              maxlength="500"
                              placeholder="Ej: Me comprometo a llevarla al veterinario, vacunarla, nunca abandonarla..."
                              oninput="contarChars(this, 'cnt-comp')">{{ old('compromiso') }}</textarea>
                    <span class="char-count" id="cnt-comp">0/500</span>
                </div>
                <div class="invalid-feedback" id="err-compromiso">⚠️ El compromiso de responsabilidad es obligatorio.</div>
            </div>

            {{-- LEY 1774 --}}
            <div class="card border-success mb-4 mt-3" style="border-radius:12px;">
                <div class="card-header bg-success text-white fw-bold" style="border-radius:12px 12px 0 0;">
                    ⚖️ Ley 1774 de 2016 — Protección Animal en Colombia
                </div>
                <div class="card-body" style="font-size:.88rem; color:#374151; line-height:1.7;">
                    <p class="mb-2">
                        La <strong>Ley 1774 de 2016</strong> reconoce a los animales como <strong>seres sintientes</strong>,
                        no como objetos. Esta ley modifica el Código Civil, el Código Penal y el Código de Procedimiento Penal
                        de Colombia, y establece:
                    </p>
                    <ul class="mb-2">
                        <li>🔒 <strong>Art. 3°</strong> — Quien adopte un animal tiene el deber de garantizarle bienestar,
                            alimentación, atención veterinaria y un ambiente adecuado.</li>
                        <li>🚫 <strong>Art. 5°</strong> — Se prohíbe el maltrato, abandono, y cualquier acto de crueldad
                            hacia los animales. El abandono es sancionado con multas y penas de prisión de 12 a 36 meses.</li>
                        <li>🏥 <strong>Art. 6°</strong> — El adoptante debe asegurar atención médica y veterinaria oportuna.</li>
                        <li>🏠 <strong>Art. 7°</strong> — El animal debe contar con condiciones de vida dignas: espacio
                            suficiente, higiene y bienestar emocional.</li>
                    </ul>
                    <p class="mb-0 text-muted" style="font-size:.82rem;">
                        Al enviar esta solicitud confirmas haber leído y aceptado las obligaciones que establece esta ley.
                        El incumplimiento puede acarrear sanciones penales y económicas.
                    </p>
                </div>
            </div>

            {{-- Aceptar ley --}}
            <div class="declaracion-check mt-2 mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="acepta_ley" name="acepta_ley" value="1"
                           style="accent-color:#16a34a; width:20px; height:20px;">
                    <label class="form-check-label ms-2 fw-semibold" for="acepta_ley" style="color:#15803d;">
                        ✅ Acepto y me comprometo a cumplir la <strong>Ley 1774 de 2016</strong> de protección animal.
                        Entiendo que el abandono o maltrato tiene consecuencias penales.
                    </label>
                </div>
                <div class="invalid-feedback" id="err-ley" style="display:none;">
                    ⚠️ Debes aceptar las condiciones de la Ley 1774 para continuar.
                </div>
            </div>

            {{-- Declaración --}}
            <div class="declaracion-check mt-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="declaro" name="declaro" value="1"
                           style="accent-color:#16a34a; width:20px; height:20px;">
                    <label class="form-check-label ms-2" for="declaro">
                        ✅ Declaro que la información proporcionada es verídica y me comprometo
                        a brindarle a la mascota un hogar seguro, amoroso y responsable.
                    </label>
                </div>
                <div class="invalid-feedback" id="err-declaro" style="display:none;">
                    ⚠️ Debes declarar que la información es verídica.
                </div>
            </div>

        </div>

        <!-- BOTÓN ENVIAR -->
        <button type="submit" class="btn-enviar" id="btnEnviar">
            🐾 Enviar Solicitud de Adopción
        </button>
        <p class="text-center text-muted small mt-3">
            Revisaremos tu solicitud y te notificaremos pronto.
        </p>

    </form>
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
<script>

// ── Selección de tipo de vivienda
function seleccionarRadio(valor) {
    document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('seleccionado'));
    document.getElementById('rc-' + valor)?.classList.add('seleccionado');
    actualizarProgreso();
}

// ── Toggle niños Sí/No
function toggleNinos(tiene) {
    document.getElementById('rc-ninos-si').classList.toggle('seleccionado', tiene);
    document.getElementById('rc-ninos-no').classList.toggle('seleccionado', !tiene);
    actualizarProgreso();
}

// ── Contadores mascotas
function cambiarCantidad(tipo, delta) {
    const span = document.getElementById('val-' + tipo);
    const inp  = document.getElementById('inp-' + tipo);
    let val = parseInt(span.textContent) + delta;
    if (val < 0) val = 0;
    if (val > 10) val = 10;
    span.textContent = val;
    inp.value = val;
    const perros = parseInt(document.getElementById('inp-perros').value);
    const gatos  = parseInt(document.getElementById('inp-gatos').value);
    document.getElementById('inp-tiene-animales').value = (perros + gatos > 0) ? 1 : 0;
    actualizarProgreso();
}

// ── Toggle campos opcionales con checkbox
function toggleCampo(checkId, campoId) {
    const check = document.getElementById(checkId);
    document.getElementById(campoId).style.display = check.checked ? 'block' : 'none';
}

// ── Conteo de caracteres
function contarChars(el, spanId) {
    document.getElementById(spanId).textContent = el.value.length + '/' + el.maxLength;
    actualizarProgreso();
}

// ── Solo números
function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

// ── Validar cédula en tiempo real
function validarCedula(input) {
    const valor = input.value;
    if (valor.length > 0 && (valor.length < 6 || valor.length > 10)) {
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
    }
    actualizarProgreso();
}

// ── Validar teléfono en tiempo real
function validarTelefono(input) {
    const valor = input.value;
    if (valor.length > 0 && valor.length !== 10) {
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
    }
    actualizarProgreso();
}

// ── Limitar rango numérico
function limitarRango(input, min, max) {
    let valor = parseInt(input.value);
    if (isNaN(valor)) return;
    if (valor < min) input.value = min;
    if (valor > max) input.value = max;
}

// ── Barra de progreso
function actualizarProgreso() {
    const campos = [
        document.getElementById('nombre_completo'),
        document.getElementById('cedula'),
        document.getElementById('telefono'),
        document.getElementById('direccion'),
        document.getElementById('ciudad'),
        document.querySelector('input[name="tipo_vivienda"]:checked'),
        document.querySelector('input[name="tiene_ninos"]:checked'),
        document.getElementById('motivo_adopcion'),
        document.getElementById('compromiso'),
        document.getElementById('acepta_ley'),
        document.getElementById('declaro'),
    ];

    let completados = 0;
    campos.forEach(el => {
        if (!el) return;
        if (el.type === 'checkbox' || el.type === 'radio') {
            if (el.checked) completados++;
        } else if (el.value.trim().length > 0) {
            completados++;
        }
    });

    const pct = Math.round((completados / campos.length) * 100);
    document.getElementById('progreso').style.width = pct + '%';
    document.getElementById('progreso-texto').textContent = pct + '% completado';
    const dots = document.querySelectorAll('.paso-dot');
    dots.forEach((d, i) => d.classList.toggle('activo', pct >= (i + 1) * 20));
}

// ── Validación al enviar
document.getElementById('formAdopcion').addEventListener('submit', function(e) {
    let valido = true;

    // Limpiar errores previos
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
    document.getElementById('alerta-campos').style.display = 'none';
    document.getElementById('lista-errores').innerHTML = '';

    const errores = [];

    function marcar(id, feedbackId, mensaje) {
        const el = document.getElementById(id);
        if (el) el.classList.add('is-invalid');
        if (feedbackId) {
            const fb = document.getElementById(feedbackId);
            if (fb) fb.style.display = 'block';
        }
        errores.push(mensaje);
        valido = false;
    }

    // Nombre
    const nombre = document.getElementById('nombre_completo');
    if (!nombre.value.trim()) marcar('nombre_completo', null, 'Nombre completo');

    // Cédula
    const cedula = document.getElementById('cedula');
    if (!cedula.value || cedula.value.length < 6 || cedula.value.length > 10) {
        marcar('cedula', 'err-cedula', 'Cédula (entre 6 y 10 dígitos)');
    }

    // Teléfono
    const telefono = document.getElementById('telefono');
    if (!telefono.value || telefono.value.length !== 10) {
        marcar('telefono', 'err-telefono', 'Teléfono (exactamente 10 dígitos)');
    }

    // Dirección
    const direccion = document.getElementById('direccion');
    if (!direccion.value.trim()) marcar('direccion', null, 'Dirección de residencia');

    // Ciudad
    const ciudad = document.getElementById('ciudad');
    if (!ciudad.value.trim()) marcar('ciudad', null, 'Ciudad');

    // Tipo de vivienda
    const vivienda = document.querySelector('input[name="tipo_vivienda"]:checked');
    if (!vivienda) {
        document.getElementById('err-vivienda').style.display = 'block';
        errores.push('Tipo de vivienda');
        valido = false;
    }

    // Niños Sí/No
    const ninos = document.querySelector('input[name="tiene_ninos"]:checked');
    if (!ninos) {
        document.getElementById('err-ninos').style.display = 'block';
        errores.push('¿Hay niños en casa?');
        valido = false;
    }

    // Motivo adopción
    const motivo = document.getElementById('motivo_adopcion');
    if (!motivo.value.trim() || motivo.value.trim().length < 20) {
        marcar('motivo_adopcion', 'err-motivo', 'Motivo de adopción (mínimo 20 caracteres)');
    }

    // Compromiso (NUEVO - obligatorio)
    const compromiso = document.getElementById('compromiso');
    if (!compromiso.value.trim()) {
        marcar('compromiso', 'err-compromiso', 'Compromiso de responsabilidad');
    }

    // Aceptar Ley 1774
    const ley = document.getElementById('acepta_ley');
    if (!ley.checked) {
        document.getElementById('err-ley').style.display = 'block';
        errores.push('Aceptar la Ley 1774 de protección animal');
        valido = false;
    }

    // Declaración veracidad
    const declaro = document.getElementById('declaro');
    if (!declaro.checked) {
        document.getElementById('err-declaro').style.display = 'block';
        errores.push('Declarar que la información es verídica');
        valido = false;
    }

    if (!valido) {
        e.preventDefault();

        // Alerta con lista igual que en los admin
        const alerta = document.getElementById('alerta-campos');
        const lista  = document.getElementById('lista-errores');
        errores.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            lista.appendChild(li);
        });
        alerta.style.display = 'block';
        alerta.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    const btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.textContent = '⏳ Enviando solicitud...';
});

// ── Limpiar errores de checkboxes al marcar
document.getElementById('acepta_ley').addEventListener('change', function() {
    document.getElementById('err-ley').style.display = this.checked ? 'none' : 'block';
});
document.getElementById('declaro').addEventListener('change', function() {
    document.getElementById('err-declaro').style.display = this.checked ? 'none' : 'block';
});

// ── Limpiar is-invalid al escribir en campos de texto
document.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(el => {
    el.addEventListener('input', function() {
        if (this.value.trim()) this.classList.remove('is-invalid');
    });
});

// ── Escuchar cambios para barra de progreso
document.querySelectorAll('input, textarea, select').forEach(el => {
    el.addEventListener('input', actualizarProgreso);
    el.addEventListener('change', actualizarProgreso);
});

// ── Inicializar contadores de caracteres y progreso
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea[maxlength]').forEach(t => {
        const spanId = t.getAttribute('oninput')?.match(/'([^']+)'/)?.[1];
        if (spanId) document.getElementById(spanId).textContent = t.value.length + '/' + t.maxLength;
    });
    actualizarProgreso();
});
</script>
</body>
</html>