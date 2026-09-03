// src/pages/AuthSliding.jsx
import { useEffect, useState } from "react";
import { useNavigate, useLocation, Link } from "react-router-dom";
import { login, registro } from "../services/authService";
import { useAuth } from "../context/AuthContext";
import "../styles/AuthSliding.css";
import logoDefault from "../assets/icono/logo de empresa.png";

const soloNumeros = (e) => {
  if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(e.key)) {
    e.preventDefault();
  }
};

// Validar formato de correo electrónico
const esEmailValido = (email) => {
  const regex = /^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
  return regex.test(email);
};

export default function AuthSliding() {
  const location = useLocation();
  const navigate = useNavigate();
  const { iniciarSesion } = useAuth();

  const [panelDerecho, setPanelDerecho] = useState(location.pathname === "/registro");

  useEffect(() => {
    setPanelDerecho(location.pathname === "/registro");
  }, [location.pathname]);

  const irARegistro = () => {
    setPanelDerecho(true);
    navigate("/registro", { replace: true });
  };

  const irALogin = () => {
    setPanelDerecho(false);
    navigate("/login", { replace: true });
  };

  // ── Login ──
  const [loginForm, setLoginForm] = useState({ identificador: "", contrasena: "" });
  const [loginError, setLoginError] = useState("");
  const [loginCargando, setLoginCargando] = useState(false);

  const handleLoginChange = (e) => setLoginForm({ ...loginForm, [e.target.name]: e.target.value });

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoginError("");
    setLoginCargando(true);

    const email = loginForm.identificador.trim();

    // Validación 1: Campo vacío
    if (!email) {
      setLoginError("📧 Ingresa tu correo electrónico.");
      setLoginCargando(false);
      return;
    }

    // Validación 2: Formato de correo válido
    if (!esEmailValido(email)) {
      setLoginError("📧 Ingresa un correo electrónico válido (ej: usuario@dominio.com).");
      setLoginCargando(false);
      return;
    }

    try {
      const res = await login(email, loginForm.contrasena);
      iniciarSesion(res.data);
      navigate("/");
    } catch (err) {
      const mensaje = err.response?.data?.mensaje || "No se pudo iniciar sesión. Intenta de nuevo.";
      
      // Mensajes personalizados según el error
      if (mensaje.includes("No existe una cuenta")) {
        setLoginError("❌ No existe una cuenta con ese correo electrónico. ¿Quieres registrarte?");
      } else if (mensaje.includes("Contraseña incorrecta")) {
        setLoginError("🔑 Contraseña incorrecta. Por favor, verifica tus credenciales.");
      } else if (mensaje.includes("correo electrónico válido")) {
        setLoginError("📧 Ingresa un correo electrónico válido.");
      } else {
        setLoginError(mensaje);
      }
    } finally {
      setLoginCargando(false);
    }
  };

  // ── Registro ──
  const [regForm, setRegForm] = useState({ nombre: "", correo: "", cedula: "", telefono: "", contrasena: "" });
  const [regErrores, setRegErrores] = useState({});
  const [regErrorGeneral, setRegErrorGeneral] = useState("");
  const [regCargando, setRegCargando] = useState(false);

  const handleRegChange = (e) => setRegForm({ ...regForm, [e.target.name]: e.target.value });

  const handleRegistro = async (e) => {
    e.preventDefault();
    setRegErrores({});
    setRegErrorGeneral("");
    setRegCargando(true);

    // Validar formato de correo en registro
    if (!esEmailValido(regForm.correo)) {
      setRegErrorGeneral("📧 Ingresa un correo electrónico válido (ej: usuario@dominio.com).");
      setRegCargando(false);
      return;
    }

    try {
      const res = await registro(
        regForm.nombre, 
        regForm.correo, 
        regForm.cedula, 
        regForm.telefono, 
        regForm.contrasena
      );
      iniciarSesion(res.data);
      navigate("/");
    } catch (err) {
      if (err.response?.status === 400 && err.response.data.errores) {
        setRegErrores(err.response.data.errores);
      } else {
        setRegErrorGeneral(err.response?.data?.mensaje || "No se pudo completar el registro.");
      }
    } finally {
      setRegCargando(false);
    }
  };

  return (
    <div className="as-fondo">
      <div className={`as-contenedor ${panelDerecho ? "as-panel-derecho" : ""}`}>

        {/* ── Formulario de Registro ── */}
        <div className="as-form-contenedor as-registro">
          <form className="as-form" onSubmit={handleRegistro}>
            <img src={logoDefault} alt="Adoptafest" className="as-logo" />
            <h1>Crear cuenta</h1>
            <span className="as-subtexto">Únete y ayuda a más mascotas a encontrar hogar</span>

            <input 
              name="nombre" 
              placeholder="Nombre completo" 
              value={regForm.nombre} 
              onChange={handleRegChange} 
              required 
            />
            {regErrores.nombre && <span className="as-error-campo">{regErrores.nombre}</span>}

            <input 
              type="email" 
              name="correo" 
              placeholder="Correo electrónico" 
              value={regForm.correo} 
              onChange={handleRegChange} 
              required 
            />
            {regErrores.correo && <span className="as-error-campo">{regErrores.correo}</span>}

            <input 
              name="cedula" 
              placeholder="Cédula" 
              value={regForm.cedula} 
              onChange={handleRegChange} 
              onKeyDown={soloNumeros} 
              maxLength={15} 
              required 
            />
            {regErrores.cedula && <span className="as-error-campo">{regErrores.cedula}</span>}

            <input 
              name="telefono" 
              placeholder="Teléfono" 
              value={regForm.telefono} 
              onChange={handleRegChange} 
              onKeyDown={soloNumeros} 
              maxLength={10} 
              required 
            />
            {regErrores.telefono && <span className="as-error-campo">{regErrores.telefono}</span>}

            <input 
              type="password" 
              name="contrasena" 
              placeholder="Contraseña (mínimo 6 caracteres)" 
              value={regForm.contrasena} 
              onChange={handleRegChange} 
              required 
            />
            {regErrores.contrasena && <span className="as-error-campo">{regErrores.contrasena}</span>}

            {regErrorGeneral && <div className="as-error-general">{regErrorGeneral}</div>}

            <button type="submit" className="as-btn" disabled={regCargando}>
              {regCargando ? "Creando cuenta..." : "Registrarme"}
            </button>

            <button type="button" className="as-link-movil" onClick={irALogin}>
              ¿Ya tienes cuenta? Inicia sesión
            </button>
          </form>
        </div>

        {/* ── Formulario de Login ── */}
        <div className="as-form-contenedor as-login">
          <form className="as-form" onSubmit={handleLogin}>
            <img src={logoDefault} alt="Adoptafest" className="as-logo" />
            <h1>Iniciar sesión</h1>
            <span className="as-subtexto">Nos alegra verte de nuevo</span>

            <input
              name="identificador"
              type="email"
              placeholder="Correo electrónico"
              value={loginForm.identificador}
              onChange={handleLoginChange}
              required
            />
            <input
              type="password"
              name="contrasena"
              placeholder="Contraseña"
              value={loginForm.contrasena}
              onChange={handleLoginChange}
              required
            />

            {loginError && <div className="as-error-general">{loginError}</div>}

            <button type="submit" className="as-btn" disabled={loginCargando}>
              {loginCargando ? "Ingresando..." : "Ingresar"}
            </button>

            <button type="button" className="as-link-movil" onClick={irARegistro}>
              ¿No tienes cuenta? Regístrate
            </button>
          </form>
        </div>

        {/* ── Overlay que se desliza ── */}
        <div className="as-overlay-contenedor">
          <div className="as-overlay">
            <div className="as-overlay-panel as-overlay-izq">
              <div className="as-tag-collar">
                <span className="as-tag-aro" />
                🐾
              </div>
              <h1>¡Hola de nuevo!</h1>
              <p>Inicia sesión para seguir ayudando a encontrarles un hogar</p>
              <button type="button" className="as-btn as-btn-fantasma" onClick={irALogin}>
                Iniciar sesión
              </button>
            </div>

            <div className="as-overlay-panel as-overlay-der">
              <div className="as-tag-collar">
                <span className="as-tag-aro" />
                🏠
              </div>
              <h1>¡Bienvenido!</h1>
              <p>Regístrate y forma parte de la comunidad Adoptafest</p>
              <button type="button" className="as-btn as-btn-fantasma" onClick={irARegistro}>
                Crear cuenta
              </button>
            </div>
          </div>
        </div>

      </div>
      <Link to="/" className="as-volver">← Volver al inicio</Link>
    </div>
  );
}