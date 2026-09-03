import { useEffect, useMemo, useState } from "react";
import {
  listarSolicitudesAdmin,
  aprobarSolicitud,
  rechazarSolicitud,
} from "../../services/adminAdopcionService";
import { useToast } from "../../context/ToastContext";
import "../../styles/AdminLayout.css";
import "../../styles/AdminSolicitudes.css";

const ESTADOS = [
  { valor: "PENDIENTE", label: "Pendiente" },
  { valor: "APROBADA", label: "Aprobadas" },
  { valor: "RECHAZADA", label: "Rechazadas" },
  { valor: "", label: "Todas" },
];

export default function AdminSolicitudes() {
  const toast = useToast();
  const [solicitudes, setSolicitudes] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [filtro, setFiltro] = useState("PENDIENTE");

  const [detalle, setDetalle] = useState(null);
  const [modoRechazo, setModoRechazo] = useState(false);
  const [motivoRechazo, setMotivoRechazo] = useState("");
  const [errorAccion, setErrorAccion] = useState("");
  const [procesando, setProcesando] = useState(false);

  const cargar = () => {
    setCargando(true);
    listarSolicitudesAdmin(filtro)
      .then((res) => setSolicitudes(res.data))
      .catch(() => toast.error("No se pudieron cargar las solicitudes."))
      .finally(() => setCargando(false));
  };

  useEffect(cargar, [filtro]);

  // ── Orden por mascota: 1°, 2°, 3°... según quién la solicitó primero.
  //    Se calcula al vuelo, así que si una mascota vuelve a quedar libre
  //    más adelante, las solicitudes nuevas reinician solas desde 1. ──
  const solicitudesOrdenadas = useMemo(() => {
    const ordenadasPorFecha = [...solicitudes].sort(
      (a, b) => new Date(a.createdAt) - new Date(b.createdAt)
    );
    const contadorPorMascota = {};
    const conPosicion = ordenadasPorFecha.map((s) => {
      const key = s.mascota?.id ?? "sin-mascota";
      contadorPorMascota[key] = (contadorPorMascota[key] || 0) + 1;
      return { ...s, posicion: contadorPorMascota[key] };
    });

    // Se muestran agrupadas: todas las de una misma mascota juntas, en orden.
    return conPosicion.sort((a, b) => {
      const nombreA = a.mascota?.nombre ?? "";
      const nombreB = b.mascota?.nombre ?? "";
      if (nombreA !== nombreB) return nombreA.localeCompare(nombreB);
      return a.posicion - b.posicion;
    });
  }, [solicitudes]);

  const abrirDetalle = (s) => {
    setDetalle(s);
    setModoRechazo(false);
    setMotivoRechazo("");
    setErrorAccion("");
  };

  const cerrarDetalle = () => setDetalle(null);

  const confirmarAprobar = async () => {
    setProcesando(true);
    setErrorAccion("");
    try {
      await aprobarSolicitud(detalle.id);
      toast.exito(`Solicitud aprobada. Ve a "Citas" para agendarle su cita virtual a ${detalle.nombreCompleto}.`);
      cerrarDetalle();
      cargar();
    } catch (err) {
      setErrorAccion(err.response?.data?.mensaje || "No se pudo aprobar la solicitud.");
    } finally {
      setProcesando(false);
    }
  };

  const confirmarRechazar = async () => {
    if (motivoRechazo.trim().length < 5) {
      setErrorAccion("Escribe un motivo (mínimo 5 caracteres) — el solicitante lo verá.");
      return;
    }
    setProcesando(true);
    setErrorAccion("");
    try {
      await rechazarSolicitud(detalle.id, motivoRechazo);
      toast.info(`Solicitud rechazada. Se notificó a ${detalle.nombreCompleto}.`);
      cerrarDetalle();
      cargar();
    } catch (err) {
      setErrorAccion(err.response?.data?.mensaje || "No se pudo rechazar la solicitud.");
    } finally {
      setProcesando(false);
    }
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Solicitudes de adopción</h1>
          <p>{solicitudes.length} en esta vista</p>
        </div>
        <div className="as-tabs">
          {ESTADOS.map((e) => (
            <button
              key={e.valor}
              className={`as-tab ${filtro === e.valor ? "activo" : ""}`}
              onClick={() => setFiltro(e.valor)}
            >
              {e.label}
            </button>
          ))}
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
          <thead>
            <tr>
              <th>Mascota</th>
              <th>Puesto</th>
              <th>Solicitante</th>
              <th>Ciudad</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="6" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && solicitudesOrdenadas.length === 0 && (
              <tr><td colSpan="6" className="admin-vacio">No hay solicitudes en esta vista.</td></tr>
            )}
            {!cargando && solicitudesOrdenadas.map((s) => (
              <tr key={s.id}>
                <td><strong>{s.mascota?.nombre ?? "—"}</strong></td>
                <td>
                  {filtro === "PENDIENTE" ? (
                    <span className="as-posicion">{s.posicion}°</span>
                  ) : "—"}
                </td>
                <td>{s.nombreCompleto}</td>
                <td>{s.ciudad}</td>
                <td>
                  <span className={`admin-badge ${
                    s.estado === "PENDIENTE" ? "admin-badge-proceso" :
                    s.estado === "APROBADA" ? "admin-badge-disponible" : "admin-badge-adoptado"
                  }`}>
                    {s.estado}
                  </span>
                </td>
                <td>
                  <button className="admin-icon-btn" onClick={() => abrirDetalle(s)} title="Ver detalle">👁</button>
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
              <h2>Solicitud de {detalle.nombreCompleto}</h2>
              <button className="admin-modal-cerrar" onClick={cerrarDetalle}>✕</button>
            </div>

            <div className="as-detalle-body">
              <div className="as-detalle-seccion">
                <h4>Mascota {filtro === "PENDIENTE" && `· Puesto ${detalle.posicion}°`}</h4>
                <p>{detalle.mascota?.nombre} — {detalle.mascota?.tipo}, {detalle.mascota?.edad} años</p>
              </div>

              <div className="as-detalle-seccion">
                <h4>Datos del solicitante</h4>
                <div className="as-detalle-grid">
                  <span><b>Cédula:</b> {detalle.cedula}</span>
                  <span><b>Teléfono:</b> {detalle.telefono}</span>
                  <span><b>Dirección:</b> {detalle.direccion}</span>
                  <span><b>Ciudad:</b> {detalle.ciudad}</span>
                </div>
              </div>

              <div className="as-detalle-seccion">
                <h4>Vivienda</h4>
                <div className="as-detalle-grid">
                  <span><b>Tipo:</b> {detalle.tipoVivienda}</span>
                  <span><b>Propia:</b> {detalle.esPropia ? "Sí" : "No"}</span>
                  <span><b>Patio:</b> {detalle.tienePatio ? "Sí" : "No"}</span>
                  <span><b>Personas en casa:</b> {detalle.personasEnCasa}</span>
                </div>
              </div>

              <div className="as-detalle-seccion">
                <h4>Convivencia</h4>
                <div className="as-detalle-grid">
                  <span><b>Niños:</b> {detalle.tieneNinos ? `Sí (${detalle.edadesNinos || "—"})` : "No"}</span>
                  <span><b>Otras mascotas:</b> {detalle.tieneOtrosAnimales ? `Sí (${detalle.cualesAnimales || "—"})` : "No"}</span>
                </div>
              </div>

              <div className="as-detalle-seccion">
                <h4>Experiencia</h4>
                <p><b>¿Ha tenido mascotas?</b> {detalle.tieneExperiencia ? "Sí" : "No"} {detalle.descripcionExperiencia && `— ${detalle.descripcionExperiencia}`}</p>
                <p><b>Horas sola al día:</b> {detalle.horasSolaMascota} {detalle.quienCuidaAusencia && `— A cargo de: ${detalle.quienCuidaAusencia}`}</p>
              </div>

              <div className="as-detalle-seccion">
                <h4>Motivo de adopción</h4>
                <p>{detalle.motivoAdopcion}</p>
              </div>

              {detalle.observaciones && (
                <div className="as-detalle-seccion">
                  <h4>Observaciones</h4>
                  <p>{detalle.observaciones}</p>
                </div>
              )}

              {detalle.estado === "PENDIENTE" && !modoRechazo && (
                <div className="as-detalle-acciones">
                  <button className="admin-btn-cancelar" style={{ background: "#fee2e2", color: "#991b1b" }} onClick={() => setModoRechazo(true)}>
                    Rechazar
                  </button>
                  <button className="admin-btn-guardar" disabled={procesando} onClick={confirmarAprobar}>
                    {procesando ? "Aprobando…" : "Aprobar solicitud"}
                  </button>
                </div>
              )}

              {detalle.estado === "PENDIENTE" && modoRechazo && (
                <div className="as-subform">
                  <h4>Motivo del rechazo</h4>
                  <div className="admin-campo">
                    <textarea
                      rows="3"
                      value={motivoRechazo}
                      onChange={(e) => setMotivoRechazo(e.target.value)}
                      placeholder="El solicitante verá este mensaje en su notificación."
                    />
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