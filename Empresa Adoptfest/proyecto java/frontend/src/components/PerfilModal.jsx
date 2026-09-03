// src/components/PerfilModal.jsx
import { useEffect, useState } from "react";
import { useAuth } from "../context/AuthContext";
import { obtenerPerfil, actualizarPerfil } from "../services/userService";
import { subirImagen } from "../services/uploadService";
import { imagenUrl } from "../services/api";
import {
  listarNotificaciones,
  marcarLeida,
  marcarTodasLeidas,
  eliminarNotificacion,
  eliminarTodasNotificaciones,
} from "../services/notificacionService";
import { misDonaciones } from "../services/donacionService";
import "../styles/PerfilModal.css";

const NIVELES = {
  oro: { label: "Oro", clase: "tag-oro", siguiente: null, umbral: 1000 },
  plata: { label: "Plata", clase: "tag-plata", siguiente: 1000, umbral: 500 },
  bronce: { label: "Bronce", clase: "tag-bronce", siguiente: 500, umbral: 200 },
  apoyo: { label: "Apoyo", clase: "tag-apoyo", siguiente: 200, umbral: 0 },
};

const ESTADO_ESTILO = {
  PENDIENTE: "estado-pendiente",
  COMPLETADO: "estado-ok",
  CONFIRMADO: "estado-ok",
  APROBADO: "estado-info",
  RECHAZADO: "estado-mal",
  FALLIDO: "estado-mal",
};

const NOTI_CONFIG = {
  EXITO: { icono: "✅", clase: "noti-exito" },
  INFO: { icono: "🐾", clase: "noti-info" },
  ALERTA: { icono: "⚠️", clase: "noti-alerta" },
  RECHAZADO: { icono: "❌", clase: "noti-rechazado" },
};

function formatearFechaNoti(fechaISO) {
  if (!fechaISO) return "";
  const f = new Date(fechaISO);
  return f.toLocaleString("es-CO", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

export default function PerfilModal() {
  const { usuario, iniciarSesion } = useAuth();
  const [tab, setTab] = useState("perfil");

  const [perfil, setPerfil] = useState(null);
  const [form, setForm] = useState({
    nombre: "",
    telefono: "",
    cedula: "",
    descripcion: "",
  });
  const [archivoImagen, setArchivoImagen] = useState(null);
  const [previewImagen, setPreviewImagen] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [mensaje, setMensaje] = useState(null);

  const [notificaciones, setNotificaciones] = useState([]);
  const [cargandoNotis, setCargandoNotis] = useState(false);
  const [borrandoTodas, setBorrandoTodas] = useState(false);

  const [donaciones, setDonaciones] = useState({ dinero: [], especie: [] });
  const [cargandoDonaciones, setCargandoDonaciones] = useState(false);

  useEffect(() => {
    const modalEl = document.getElementById("modalPerfil");
    if (!modalEl) return;
    const alAbrir = () => cargarPerfil();
    modalEl.addEventListener("shown.bs.modal", alAbrir);
    return () => modalEl.removeEventListener("shown.bs.modal", alAbrir);
  }, []);

  const cargarPerfil = async () => {
    try {
      const res = await obtenerPerfil();
      setPerfil(res.data);
      setForm({
        nombre: res.data.name ?? "",
        telefono: res.data.telefono ?? "",
        cedula: res.data.cedula ?? "",
        descripcion: res.data.descripcion ?? "",
      });
      setPreviewImagen(null);
      setArchivoImagen(null);
    } catch {
      setMensaje({ tipo: "error", texto: "No se pudo cargar tu perfil." });
    }
  };

  const cargarNotificaciones = async () => {
    setCargandoNotis(true);
    try {
      const res = await listarNotificaciones();
      setNotificaciones(res.data);
    } finally {
      setCargandoNotis(false);
    }
  };

  const cargarDonaciones = async () => {
    setCargandoDonaciones(true);
    try {
      const res = await misDonaciones();
      setDonaciones(res.data);
    } finally {
      setCargandoDonaciones(false);
    }
  };

  const cambiarTab = async (nuevoTab) => {
    setTab(nuevoTab);
    setMensaje(null);
    if (nuevoTab === "notificaciones") {
      cargarNotificaciones();
    }
    if (nuevoTab === "donaciones" && donaciones.dinero.length === 0 && donaciones.especie.length === 0) {
      cargarDonaciones();
    }
  };

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const soloNumeros = (e) => {
    if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(e.key)) {
      e.preventDefault();
    }
  };

  const handleSeleccionarImagen = (e) => {
    const archivo = e.target.files[0];
    if (!archivo) return;
    setArchivoImagen(archivo);
    setPreviewImagen(URL.createObjectURL(archivo));
  };

  const guardarPerfil = async (e) => {
    e.preventDefault();
    setGuardando(true);
    setMensaje(null);
    try {
      let urlFoto;
      if (archivoImagen) {
        const resSubida = await subirImagen(archivoImagen);
        urlFoto = resSubida.data.url;
      }
      
      const res = await actualizarPerfil({ 
        nombre: form.nombre, 
        telefono: form.telefono, 
        foto: urlFoto,
        descripcion: form.descripcion 
      });
      setPerfil(res.data);
      setArchivoImagen(null);
      setPreviewImagen(null);
      iniciarSesion({
        ...usuario,
        nombre: res.data.name,
        foto: res.data.foto ?? urlFoto ?? usuario?.foto ?? null,
      });
      setMensaje({ tipo: "ok", texto: "Perfil actualizado correctamente." });
    } catch (err) {
      setMensaje({ tipo: "error", texto: err.response?.data?.mensaje || "No se pudo guardar los cambios." });
    } finally {
      setGuardando(false);
    }
  };

  const handleMarcarLeida = async (id) => {
    try {
      await marcarLeida(id);
      setNotificaciones((prev) => prev.map((n) => (n.id === id ? { ...n, leida: true } : n)));
      window.dispatchEvent(new Event("notificaciones-actualizadas"));
    } catch {
      setMensaje({ tipo: "error", texto: "No se pudo marcar como leída." });
    }
  };

  const handleMarcarTodasLeidas = async () => {
    try {
      await marcarTodasLeidas();
      setNotificaciones((prev) => prev.map((n) => ({ ...n, leida: true })));
      window.dispatchEvent(new Event("notificaciones-actualizadas"));
    } catch {
      setMensaje({ tipo: "error", texto: "No se pudieron marcar todas como leídas." });
    }
  };

  const handleEliminarNotificacion = async (id) => {
    try {
      await eliminarNotificacion(id);
      setNotificaciones((prev) => prev.filter((n) => n.id !== id));
      window.dispatchEvent(new Event("notificaciones-actualizadas"));
    } catch {
      setMensaje({ tipo: "error", texto: "No se pudo eliminar la notificación." });
    }
  };

  const handleEliminarTodas = async () => {
    if (!window.confirm("¿Seguro que deseas eliminar todas tus notificaciones?")) return;
    setBorrandoTodas(true);
    try {
      await eliminarTodasNotificaciones();
      setNotificaciones([]);
      window.dispatchEvent(new Event("notificaciones-actualizadas"));
    } catch {
      setMensaje({ tipo: "error", texto: "No se pudieron eliminar todas las notificaciones." });
    } finally {
      setBorrandoTodas(false);
    }
  };

  if (!perfil) {
    return (
      <ModalWrapper>
        <div className="pf-loading">
          <div className="pf-spinner" />
        </div>
      </ModalWrapper>
    );
  }

  const nivelKey = calcularNivel(perfil.puntosDonante);
  const nivel = NIVELES[nivelKey];
  const progreso = calcularProgreso(perfil.puntosDonante);
  const noLeidasCount = notificaciones.filter((n) => !n.leida).length;

  return (
    <ModalWrapper>
      <div className="pf-header">
        <div className="pf-avatar-frame">
          {previewImagen || perfil.foto ? (
            <img src={imagenUrl(previewImagen || perfil.foto)} alt="avatar" />
          ) : (
            <span>{perfil.name?.charAt(0).toUpperCase()}</span>
          )}
          <PetTag clase={nivel.clase} label={nivel.label} />
        </div>
        <div className="pf-header-info">
          <h5>{perfil.name}</h5>
          <span className="pf-header-email">{perfil.email}</span>
          <div className="pf-header-badges">
            <span className="pf-badge-rol">
              {perfil.rol === "ADMIN" ? "🛡️ Administrador" : "👤 Adoptante"}
            </span>
            <span className="pf-puntos">
              <span className="pf-puntos-num">{perfil.puntosDonante ?? 0}</span> pts de donante
            </span>
          </div>
        </div>
      </div>

      <div className="pf-body">
        <nav className="pf-sidebar">
          <TabBtn activo={tab === "perfil"} onClick={() => cambiarTab("perfil")} label="Mi perfil" icono="👤" />
          <TabBtn
            activo={tab === "notificaciones"}
            onClick={() => cambiarTab("notificaciones")}
            label="Notificaciones"
            icono="🔔"
            badge={noLeidasCount > 0 ? noLeidasCount : null}
          />
          <TabBtn activo={tab === "donaciones"} onClick={() => cambiarTab("donaciones")} label="Donaciones" icono="💖" />
        </nav>

        <div className="pf-contenido">
          {tab === "perfil" && (
            <form onSubmit={guardarPerfil} className="pf-form">
              <div className="pf-foto-seccion">
                <div className="pf-foto-preview">
                  {previewImagen || perfil.foto ? (
                    <img src={imagenUrl(previewImagen || perfil.foto)} alt="Foto perfil" />
                  ) : (
                    <span>{perfil.name?.charAt(0).toUpperCase()}</span>
                  )}
                </div>
                <label className="pf-btn-secundario pf-cambiar-foto">
                  📷 Cambiar foto de perfil
                  <input type="file" accept="image/*" hidden onChange={handleSeleccionarImagen} />
                </label>
              </div>

              <div className="pf-grid-2">
                <div className="pf-campo">
                  <label>Nombre completo *</label>
                  <input
                    name="nombre"
                    value={form.nombre}
                    onChange={handleChange}
                    required
                    minLength={2}
                    placeholder="Tu nombre y apellido"
                  />
                </div>

                <div className="pf-campo">
                  <label>Correo electrónico (cuenta)</label>
                  <input
                    value={perfil.email || ""}
                    disabled
                    className="pf-input-disabled"
                    title="El correo está vinculado a tu cuenta"
                  />
                </div>
              </div>

              <div className="pf-grid-2">
                <div className="pf-campo">
                  <label>Cédula de ciudadanía</label>
                  <input
                    name="cedula"
                    value={form.cedula}
                    disabled
                    className="pf-input-disabled"
                    title="La cédula no se puede modificar. Si necesitas cambiarla, contacta al administrador."
                  />
                  
                </div>

                <div className="pf-campo">
                  <label>Teléfono / WhatsApp</label>
                  <input
                    name="telefono"
                    value={form.telefono}
                    onChange={handleChange}
                    onKeyDown={soloNumeros}
                    maxLength={15}
                    placeholder="Ej: 3001234567"
                  />
                </div>
              </div>

              <div className="pf-campo">
                <label>Sobre mí / Descripción</label>
                <textarea
                  name="descripcion"
                  rows="3"
                  maxLength={300}
                  value={form.descripcion}
                  onChange={handleChange}
                  placeholder="Cuéntanos un poco sobre ti, tu hogar o tu amor por los animales..."
                />
                <span className="pf-caracteres">{form.descripcion.length}/300 caracteres</span>
              </div>

              {mensaje && <div className={`pf-mensaje pf-mensaje-${mensaje.tipo}`}>{mensaje.texto}</div>}

              <button type="submit" className="pf-btn-primario" disabled={guardando}>
                {guardando ? "Guardando cambios…" : "Guardar cambios"}
              </button>
            </form>
          )}

          {tab === "notificaciones" && (
            <div className="pf-notis-contenedor">
              <div className="pf-notis-header">
                <div>
                  <h6 className="pf-notis-titulo">Bandeja de Notificaciones</h6>
                  <p className="pf-notis-subtitulo">
                    {notificaciones.length === 0
                      ? "Sin mensajes"
                      : `${notificaciones.length} ${notificaciones.length === 1 ? "notificación" : "notificaciones"}${noLeidasCount > 0 ? ` (${noLeidasCount} sin leer)` : ""}`}
                  </p>
                </div>
                <div className="pf-notis-acciones-top">
                  {noLeidasCount > 0 && (
                    <button
                      type="button"
                      className="pf-btn-noti-accion"
                      onClick={handleMarcarTodasLeidas}
                      title="Marcar todas como leídas"
                    >
                      ✓ Marcar leídas
                    </button>
                  )}
                  {notificaciones.length > 0 && (
                    <button
                      type="button"
                      className="pf-btn-borrar-todas"
                      onClick={handleEliminarTodas}
                      disabled={borrandoTodas}
                      title="Eliminar todas las notificaciones"
                    >
                      🗑️ Borrar todas
                    </button>
                  )}
                </div>
              </div>

              {cargandoNotis && (
                <div className="pf-vacio-noti">
                  <div className="pf-spinner" />
                  <p>Cargando notificaciones…</p>
                </div>
              )}

              {!cargandoNotis && notificaciones.length === 0 && (
                <div className="pf-vacio-noti">
                  <span className="pf-vacio-icono">📭</span>
                  <strong>No tienes notificaciones</strong>
                  <p>Aquí te avisaremos sobre el estado de tus solicitudes de adopción, citas y eventos.</p>
                </div>
              )}

              {!cargandoNotis && notificaciones.length > 0 && (
                <div className="pf-notis-lista">
                  {notificaciones.map((n) => {
                    const cfg = NOTI_CONFIG[n.tipo] ?? NOTI_CONFIG.INFO;
                    return (
                      <div
                        key={n.id}
                        className={`pf-noti-card ${cfg.clase} ${n.leida ? "leida" : "no-leida"}`}
                        onClick={() => !n.leida && handleMarcarLeida(n.id)}
                      >
                        <div className="pf-noti-icono-wrap">
                          <span className="pf-noti-icono">{cfg.icono}</span>
                        </div>

                        <div className="pf-noti-cuerpo">
                          <div className="pf-noti-arriba">
                            <h5 className="pf-noti-asunto">{n.titulo}</h5>
                            {!n.leida && <span className="pf-badge-nueva">Nueva</span>}
                          </div>
                          <p className="pf-noti-texto">{n.mensaje}</p>
                          <span className="pf-noti-hora">🕒 {formatearFechaNoti(n.createdAt)}</span>
                        </div>

                        <button
                          type="button"
                          className="pf-noti-btn-eliminar"
                          title="Eliminar notificación"
                          aria-label={`Eliminar notificación: ${n.titulo}`}
                          onClick={(e) => {
                            e.stopPropagation();
                            handleEliminarNotificacion(n.id);
                          }}
                        >
                          🗑️
                        </button>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          )}

          {tab === "donaciones" && (
            <div>
              <div className="pf-progreso-track">
                {["apoyo", "bronce", "plata", "oro"].map((k) => (
                  <span
                    key={k}
                    className={`pf-progreso-hito ${nivelKey === k ? "activo" : ""} ${NIVELES[k].clase}`}
                    title={NIVELES[k].label}
                  />
                ))}
                <div className="pf-progreso-fill" style={{ width: `${progreso}%` }} />
              </div>
              <p className="pf-progreso-texto">
                {nivel.siguiente
                  ? `Faltan ${nivel.siguiente - (perfil.puntosDonante ?? 0)} puntos para nivel ${NIVELES[siguienteNivel(nivelKey)].label}`
                  : "¡Nivel máximo alcanzado! 🏅"}
              </p>

              {cargandoDonaciones && <p className="pf-vacio">Cargando donaciones…</p>}

              {!cargandoDonaciones && (
                <>
                  <h6 className="pf-subtitulo">Dinero</h6>
                  {donaciones.dinero.length === 0 && <p className="pf-vacio">Sin donaciones de dinero aún.</p>}
                  {donaciones.dinero.map((d) => (
                    <div key={d.id} className="pf-donacion">
                      <span className="pf-donacion-monto">
                        ${d.monto} {d.moneda}
                      </span>
                      <span className={`pf-estado ${ESTADO_ESTILO[d.estado]}`}>{d.estado}</span>
                    </div>
                  ))}

                  <h6 className="pf-subtitulo">Especie</h6>
                  {donaciones.especie.length === 0 && <p className="pf-vacio">Sin donaciones en especie aún.</p>}
                  {donaciones.especie.map((d) => (
                    <div key={d.id} className="pf-donacion">
                      <span className="pf-donacion-monto">
                        {d.categoria} × {d.cantidad}
                      </span>
                      <span className={`pf-estado ${ESTADO_ESTILO[d.estado]}`}>{d.estado}</span>
                    </div>
                  ))}
                </>
              )}
            </div>
          )}
        </div>
      </div>
    </ModalWrapper>
  );
}

function TabBtn({ activo, onClick, label, icono = "🐾", badge = null }) {
  return (
    <button className={`pf-tab ${activo ? "activo" : ""}`} onClick={onClick} type="button">
      <span className="pf-tab-huella">{icono}</span>
      <span className="pf-tab-label">{label}</span>
      {badge != null && <span className="pf-tab-badge">{badge}</span>}
    </button>
  );
}

function PetTag({ clase, label }) {
  return (
    <div className={`pet-tag ${clase}`}>
      <span className="pet-tag-aro" />
      <span className="pet-tag-texto">{label}</span>
    </div>
  );
}

function ModalWrapper({ children }) {
  return (
    <div className="modal fade" id="modalPerfil" tabIndex="-1">
      <div className="modal-dialog modal-dialog-centered modal-lg">
        <div className="modal-content pf-modal">
          <button type="button" className="btn-close pf-cerrar" data-bs-dismiss="modal" />
          {children}
        </div>
      </div>
    </div>
  );
}

function calcularNivel(puntos = 0) {
  if (puntos >= 1000) return "oro";
  if (puntos >= 500) return "plata";
  if (puntos >= 200) return "bronce";
  return "apoyo";
}

function siguienteNivel(actual) {
  const orden = ["apoyo", "bronce", "plata", "oro"];
  const i = orden.indexOf(actual);
  return orden[Math.min(i + 1, orden.length - 1)];
}

function calcularProgreso(puntos = 0) {
  if (puntos >= 1000) return 100;
  if (puntos >= 500) return ((puntos - 500) / (1000 - 500)) * 100;
  if (puntos >= 200) return ((puntos - 200) / (500 - 200)) * 100;
  return (puntos / 200) * 100;
}