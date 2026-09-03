// src/components/AdminLayout.jsx
import { useState } from "react";
import { NavLink, Outlet, Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { imagenUrl } from "../services/api";
import "../styles/AdminLayout.css";
import logoDefault from "../assets/icono/logo de empresa.png";

const SECCIONES = [
  { to: "/admin/mascotas", icono: "🐾", label: "Mascotas" },
  { to: "/admin/eventos", icono: "🎉", label: "Eventos" },
  { to: "/admin/inscripciones", icono: "🎟️", label: "Inscripciones" },
  { to: "/admin/solicitudes", icono: "📋", label: "Solicitudes" },
  { to: "/admin/citas", icono: "📅", label: "Citas" },
  { to: "/admin/donaciones", icono: "💜", label: "Donaciones" },
  { to: "/admin/reportes", icono: "📊", label: "Informes" },
  { to: "/admin/usuarios", icono: "👥", label: "Usuarios" },
];

export default function AdminLayout() {
  const { usuario, cerrarSesion } = useAuth();
  const [colapsado, setColapsado] = useState(false);

  const fotoUrl = imagenUrl(usuario?.foto);

  return (
    <div className="ad-shell">
      <aside className={`ad-sidebar ${colapsado ? "colapsado" : ""}`}>
        <div className="ad-sidebar-top">
          <Link to="/" className="ad-logo">
            <img src={logoDefault} alt="Adoptafest" />
          </Link>
          <button className="ad-toggle" onClick={() => setColapsado(!colapsado)}>
            {colapsado ? "›" : "‹"}
          </button>
        </div>

        <nav className="ad-nav">
          {SECCIONES.map((s) => (
            <NavLink
              key={s.to}
              to={s.to}
              className={({ isActive }) => `ad-nav-item ${isActive ? "activo" : ""}`}
              title={s.label}
            >
              <span className="ad-nav-icono">{s.icono}</span>
              <span className="ad-nav-label">{s.label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="ad-sidebar-bottom">
          <Link to="/" className="ad-nav-item ad-volver">
            <span className="ad-nav-icono">🏠</span>
            <span className="ad-nav-label">Volver al sitio</span>
          </Link>

          <div className="ad-perfil-mini" title={usuario?.nombre}>
            <div className="ad-perfil-avatar">
              {fotoUrl ? <img src={fotoUrl} alt="" /> : usuario?.nombre?.charAt(0).toUpperCase()}
            </div>
            <span className="ad-perfil-nombre">{usuario?.nombre}</span>
          </div>
          <button className="ad-logout" onClick={cerrarSesion} title="Salir del panel">
            <span className="ad-nav-icono">⏻</span>
            <span className="ad-nav-label">Cerrar sesión</span>
          </button>
        </div>
      </aside>

      <main className={`ad-contenido ${colapsado ? "expandido" : ""}`}>
        <Outlet />
      </main>
    </div>
  );
}