import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { listarInscripcionesAdmin, aceptarInscripcion, rechazarInscripcion } from "../../services/adminInscripcionService";
import { listarEventosAdmin } from "../../services/adminEventoService";
import { useToast } from "../../context/ToastContext";
import "../../styles/AdminLayout.css";
import "../../styles/AdminSolicitudes.css";

const ESTADOS = [
  { valor: "PENDIENTE", label: "Pendiente " },
  { valor: "CONFIRMADA", label: "Confirmadas" },
  { valor: "CANCELADA", label: "Rechazadas" },
  { valor: "", label: "Todas" },
];

export default function AdminInscripciones() {
  const toast = useToast();
  const [searchParams, setSearchParams] = useSearchParams();
  const eventoIdFiltro = searchParams.get("eventoId") || "";

  const [eventos, setEventos] = useState([]);
  const [inscripciones, setInscripciones] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [filtroEstado, setFiltroEstado] = useState("PENDIENTE");

  const [detalle, setDetalle] = useState(null);
  const [modoRechazo, setModoRechazo] = useState(false);
  const [motivoRechazo, setMotivoRechazo] = useState("");
  const [errorAccion, setErrorAccion] = useState("");
  const [procesando, setProcesando] = useState(false);

  useEffect(() => {
    listarEventosAdmin().then((res) => setEventos(res.data));
  }, []);

  const cargar = () => {
    setCargando(true);
    listarInscripcionesAdmin(eventoIdFiltro || undefined)
      .then((res) => setInscripciones(res.data))
      .catch(() => toast.error("No se pudieron cargar las inscripciones."))
      .finally(() => setCargando(false));
  };

  useEffect(cargar, [eventoIdFiltro]);

  const cambiarFiltroEvento = (id) => {
    if (id) setSearchParams({ eventoId: id });
    else setSearchParams({});
  };

  const inscripcionesFiltradas = inscripciones.filter(
    (i) => filtroEstado === "" || i.estado === filtroEstado
  );

  const totalAsistentes = inscripcionesFiltradas
    .filter((i) => i.estado !== "CANCELADA")
    .reduce((sum, i) => sum + (i.llevaInvitado ? 2 : 1), 0);

  const abrirDetalle = (i) => {
    setDetalle(i);
    setModoRechazo(false);
    setMotivoRechazo("");
    setErrorAccion("");
  };

  const cerrarDetalle = () => setDetalle(null);

  const confirmarAceptar = async () => {
    setProcesando(true);
    setErrorAccion("");
    try {
      await aceptarInscripcion(detalle.id);
      toast.exito(`Inscripción aceptada. Se notificó a ${detalle.user?.name}.`);
      cerrarDetalle();
      cargar();
    } catch (err) {
      setErrorAccion(err.response?.data?.mensaje || "No se pudo aceptar la inscripción.");
    } finally {
      setProcesando(false);
    }
  };

  const confirmarRechazar = async () => {
    if (motivoRechazo.trim().length < 5) {
      setErrorAccion("Escribe un motivo (mínimo 5 caracteres) — el usuario lo verá.");
      return;
    }
    setProcesando(true);
    setErrorAccion("");
    try {
      await rechazarInscripcion(detalle.id, motivoRechazo);
      toast.info(`Inscripción rechazada. Se notificó a ${detalle.user?.name}.`);
      cerrarDetalle();
      cargar();
    } catch (err) {
      setErrorAccion(err.response?.data?.mensaje || "No se pudo rechazar la inscripción.");
    } finally {
      setProcesando(false);
    }
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Inscripciones a eventos</h1>
          <p>{inscripcionesFiltradas.length} en esta vista · {totalAsistentes} personas en total</p>
        </div>
        <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
          <select className="admin-buscador" value={eventoIdFiltro} onChange={(e) => cambiarFiltroEvento(e.target.value)}>
            <option value="">Todos los eventos</option>
            {eventos.map((ev) => <option key={ev.id} value={ev.id}>{ev.titulo}</option>)}
          </select>
          <div className="as-tabs">
            {ESTADOS.map((e) => (
              <button key={e.valor} className={`as-tab ${filtroEstado === e.valor ? "activo" : ""}`} onClick={() => setFiltroEstado(e.valor)}>
                {e.label}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
          <thead>
            <tr>
              <th>Evento</th>
              <th>Titular</th>
              <th>Invitado</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="5" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && inscripcionesFiltradas.length === 0 && (
              <tr><td colSpan="5" className="admin-vacio">No hay inscripciones en esta vista.</td></tr>
            )}
            {!cargando && inscripcionesFiltradas.map((i) => (
              <tr key={i.id}>
                <td>{i.evento?.titulo}</td>
                <td><strong>{i.user?.name}</strong></td>
                <td>{i.llevaInvitado ? `🧑‍🤝‍🧑 ${i.nombreInvitado}` : "—"}</td>
                <td>
                  <span className={`admin-badge ${
                    i.estado === "PENDIENTE" ? "admin-badge-pendiente-fuerte" :
                    i.estado === "CONFIRMADA" ? "admin-badge-disponible" : "admin-badge-mal"
                  }`}>
                    {i.estado === "PENDIENTE" ? "⏳ Pendiente" : i.estado === "CONFIRMADA" ? "✓ Confirmada" : "✕ Rechazada"}
                  </span>
                </td>
                <td>
                  <button className="admin-icon-btn" onClick={() => abrirDetalle(i)} title="Ver detalle">👁</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {detalle && (
        <div className="admin-modal-overlay" onClick={cerrarDetalle}>
          <div className="admin-modal as-modal-detalle" onClick={(e) => e.stopPropagation()}>
            <div className="admin-modal-header">
              <h2>Inscripción — {detalle.evento?.titulo}</h2>
              <button className="admin-modal-cerrar" onClick={cerrarDetalle}>✕</button>
            </div>

            <div className="as-detalle-body">
              <div className="as-detalle-seccion">
                <h4>Titular (información de su cuenta)</h4>
                <div className="as-detalle-grid">
                  <span><b>Nombre:</b> {detalle.user?.name}</span>
                  <span><b>Correo:</b> {detalle.user?.email}</span>
                  <span><b>Teléfono:</b> {detalle.user?.telefono ?? "—"}</span>
                </div>
              </div>

              <div className="as-detalle-seccion">
                <h4>Invitado</h4>
                {detalle.llevaInvitado ? (
                  <div className="as-tarjeta-invitado">
                    <div className="as-tarjeta-invitado-titulo">🧑‍🤝‍🧑 Va acompañado</div>
                    <div className="as-detalle-grid">
                      <span><b>Nombre:</b> {detalle.nombreInvitado}</span>
                      <span><b>Correo:</b> {detalle.correoInvitado}</span>
                      <span><b>Documento:</b> {detalle.tipoDocumentoInvitado} {detalle.cedulaInvitado}</span>
                    </div>
                  </div>
                ) : (
                  <p style={{ color: "var(--texto-suave)", fontSize: 13 }}>No lleva invitado.</p>
                )}
              </div>

              {detalle.motivoRechazo && (
                <div className="as-detalle-seccion">
                  <h4>Motivo del rechazo</h4>
                  <p>{detalle.motivoRechazo}</p>
                </div>
              )}

              {detalle.estado === "PENDIENTE" && !modoRechazo && (
                <div className="as-detalle-acciones">
                  <button className="admin-btn-cancelar" style={{ background: "#fee2e2", color: "#991b1b" }} onClick={() => setModoRechazo(true)}>
                    Rechazar
                  </button>
                  <button className="admin-btn-guardar" disabled={procesando} onClick={confirmarAceptar}>
                    {procesando ? "Aceptando…" : "Aceptar inscripción"}
                  </button>
                </div>
              )}

              {detalle.estado === "PENDIENTE" && modoRechazo && (
                <div className="as-subform">
                  <h4>Motivo del rechazo</h4>
                  <div className="admin-campo">
                    <textarea rows="3" value={motivoRechazo} onChange={(e) => setMotivoRechazo(e.target.value)} placeholder="El usuario verá este mensaje en su notificación." />
                  </div>
                  {errorAccion && <div className="admin-modal-error">{errorAccion}</div>}
                  <div className="as-detalle-acciones">
                    <button className="admin-btn-cancelar" onClick={() => setModoRechazo(false)}>Cancelar</button>
                    <button className="admin-btn-guardar" style={{ background: "#dc2626" }} disabled={procesando} onClick={confirmarRechazar}>
                      {procesando ? "Enviando…" : "Confirmar rechazo"}
                    </button>
                  </div>
                </div>
              )}

              {errorAccion && !modoRechazo && <div className="admin-modal-error" style={{ marginTop: 10 }}>{errorAccion}</div>}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}