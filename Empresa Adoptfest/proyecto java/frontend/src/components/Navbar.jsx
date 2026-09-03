import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { contarNoLeidas, marcarTodasLeidas } from "../services/notificacionService";
import { imagenUrl } from "../services/api";
import "../styles/Navbar.css";
import logoDefault from "../assets/icono/logo de empresa.png";

export default function Navbar({
  logoUrl = logoDefault,
  onAbrirPerfil = () => {},
}) {
  const { usuario, cerrarSesion } = useAuth();
  const navigate = useNavigate();
  const [noLeidasCount, setNoLeidasCount] = useState(0);
  const [conScroll, setConScroll] = useState(false);

  useEffect(() => {
    if (!usuario) return;
    const cargarContador = () => contarNoLeidas()
      .then((res) => setNoLeidasCount(res.data.total))
      .catch(() => setNoLeidasCount(0));
    cargarContador();
    window.addEventListener("notificaciones-actualizadas", cargarContador);
    return () => window.removeEventListener("notificaciones-actualizadas", cargarContador);
  }, [usuario]);

  // Detalle de interacción: la isla se compacta y se vuelve más sólida al bajar
  useEffect(() => {
    const alScrollear = () => setConScroll(window.scrollY > 30);
    window.addEventListener("scroll", alScrollear);
    return () => window.removeEventListener("scroll", alScrollear);
  }, []);

  const handleLogout = (e) => {
    e.preventDefault();
    cerrarSesion();
    navigate("/login");
  };

  const quitarAviso = async (e) => {
    e.stopPropagation();
    try {
      await marcarTodasLeidas();
      setNoLeidasCount(0);
      window.dispatchEvent(new Event("notificaciones-actualizadas"));
    } catch {
      // El contador se volverá a consultar en la siguiente actualización.
    }
  };

  const nombre = usuario?.nombre ?? null;
  const rol = usuario?.rol ?? null;
  const foto = usuario?.foto ?? null;
  const fotoUrl = imagenUrl(foto);

  return (
    <div className={`nb-wrapper ${conScroll ? "nb-scroll" : ""}`}>
      <nav className="nb-isla navbar navbar-expand-lg navbar-dark">
        <div className="nb-contenido">
          <Link className="nb-brand" to="/">
            <span className="nb-brand-tag">
              <span className="nb-brand-aro" />
              <img src={logoUrl} alt="Logo" className="nb-logo" />
            </span>
            <span className="nb-brand-texto">Adoptafest</span>
          </Link>

          <button
            className="navbar-toggler nb-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menu"
          >
            <span className="nb-toggler-linea" />
            <span className="nb-toggler-linea" />
            <span className="nb-toggler-linea" />
          </button>

          <div className="collapse navbar-collapse" id="menu">
            <ul className="nb-links">
              <li><Link className="nb-link" to="/eventos"><span>Eventos</span></Link></li>
              <li><Link className="nb-link" to="/adopcion"><span>Adoptar</span></Link></li>
              <li><Link className="nb-link" to="/donaciones"><span>Donante</span></Link></li>
            </ul>

            <div className="nb-derecha">
              {nombre ? (
                <div className="nb-usuario">
                  {rol === "ADMIN" && (
                    <Link to="/admin" className="nb-admin">
                      <span className="nb-admin-punto" />
                      Administrador
                    </Link>
                  )}

                  <button
                    className="nb-perfil"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPerfil"
                    onClick={onAbrirPerfil}
                  >
                    <span className="nb-perfil-avatar">
                      {fotoUrl ? (
                        <img src={fotoUrl} alt="foto" />
                      ) : (
                        nombre.charAt(0).toUpperCase()
                      )}
                      {noLeidasCount > 0 && (
                        <span
                          className="nb-badge"
                          role="button"
                          tabIndex={0}
                          title="Marcar notificaciones como leídas"
                          aria-label="Marcar notificaciones como leídas"
                          onClick={quitarAviso}
                          onKeyDown={(e) => e.key === "Enter" && quitarAviso(e)}
                        >
                          {noLeidasCount}
                        </span>
                      )}
                    </span>
                    <span className="nb-perfil-nombre">{nombre}</span>
                  </button>

                  <button type="button" className="nb-logout" onClick={handleLogout} title="Cerrar sesión">
                    ⏻
                  </button>
                </div>
              ) : (
                <Link to="/login" className="nb-login">
                  Ingresar
                  <span className="nb-login-flecha">→</span>
                </Link>
              )}
            </div>
          </div>
        </div>
      </nav>
    </div>
  );
}