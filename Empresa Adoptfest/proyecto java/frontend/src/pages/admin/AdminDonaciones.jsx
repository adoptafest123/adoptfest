// src/pages/admin/AdminDonaciones.jsx
import { useEffect, useState } from "react";
import { 
  listarDonacionesAdmin, 
  aprobarDonacionEspecie, 
  rechazarDonacionEspecie,
  confirmarDonacionEspecie,
  eliminarDonacionEspecie
} from "../../services/donacionService";
import { useToast } from "../../context/ToastContext";
import ConfirmModal from "../../components/ConfirmModal";
import "../../styles/AdminLayout.css";

export default function AdminDonaciones() {
  const toast = useToast();
  const [cargando, setCargando] = useState(true);
  const [donaciones, setDonaciones] = useState({ especies: [], dineros: [] });
  const [confirmando, setConfirmando] = useState(null);
  const [accionConfirmar, setAccionConfirmar] = useState(null);

  useEffect(() => {
    cargar();
  }, []);

  const cargar = () => {
    setCargando(true);
    listarDonacionesAdmin()
      .then((res) => setDonaciones(res.data))
      .catch(() => toast.error("No se pudieron cargar las donaciones."))
      .finally(() => setCargando(false));
  };

  const manejarAprobar = (id) => {
    setConfirmando(id);
    setAccionConfirmar("aprobar");
  };

  const manejarRechazar = (id) => {
    setConfirmando(id);
    setAccionConfirmar("rechazar");
  };

  const manejarConfirmar = (id) => {
    setConfirmando(id);
    setAccionConfirmar("confirmar");
  };

  const manejarEliminar = (id) => {
    setConfirmando(id);
    setAccionConfirmar("eliminar");
  };

  const confirmarAccion = async () => {
    try {
      if (accionConfirmar === "aprobar") {
        await aprobarDonacionEspecie(confirmando);
        toast.exito("Donación aprobada. Ya se puede agendar la recolección.");
      } else if (accionConfirmar === "rechazar") {
        await rechazarDonacionEspecie(confirmando);
        toast.exito("Donación rechazada. El usuario será notificado.");
      } else if (accionConfirmar === "confirmar") {
        await confirmarDonacionEspecie(confirmando);
        toast.exito("Donación confirmada. Se otorgaron los puntos al usuario.");
      } else if (accionConfirmar === "eliminar") {
        await eliminarDonacionEspecie(confirmando);
        toast.exito("Donación eliminada permanentemente.");
      }
      setConfirmando(null);
      cargar();
    } catch (error) {
      toast.error(error.response?.data?.mensaje || "Error al procesar la donación.");
    }
  };

  const getEstadoBadge = (estado) => {
    const clases = {
      PENDIENTE: "admin-badge-pendiente-fuerte",
      APROBADO: "admin-badge-disponible",
      CONFIRMADO: "admin-badge-disponible",
      RECHAZADO: "admin-badge-mal",
    };
    return `admin-badge ${clases[estado] || ""}`;
  };

  const getEstadoLabel = (estado) => {
    const labels = {
      PENDIENTE: "⏳ Pendiente",
      APROBADO: "✅ Aprobado",
      CONFIRMADO: "📦 Confirmado",
      RECHAZADO: "❌ Rechazado",
    };
    return labels[estado] || estado;
  };

  const formatearFecha = (fechaISO) => {
    if (!fechaISO) return "—";
    return new Date(fechaISO).toLocaleString("es-CO", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Donaciones</h1>
          <p>Gestiona las donaciones en especie y en dinero</p>
        </div>
        <button className="admin-btn-nuevo" onClick={cargar}>
          🔄 Refrescar
        </button>
      </div>

      {cargando && <div className="admin-vacio">Cargando...</div>}

      {!cargando && (
        <>
          {/* Donaciones en Dinero */}
          <h3 style={{ marginTop: "24px", marginBottom: "12px" }}>💰 Donaciones en Dinero</h3>
          <div className="admin-card" style={{ overflowX: "auto" }}>
            <table className="admin-tabla">
              <thead>
                <tr>
                  <th>Donante</th>
                  <th>Cédula</th>
                  <th>Monto</th>
                  <th>Moneda</th>
                  <th>Fecha</th>
                  <th>Puntos</th>
                  <th>Refugio</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                {donaciones.dineros?.length === 0 && (
                  <tr><td colSpan="8" className="admin-vacio">No hay donaciones en dinero</td></tr>
                )}
                {donaciones.dineros?.map((d) => (
                  <tr key={d.id}>
                    <td><strong>{d.user?.name || "Usuario eliminado"}</strong></td>
                    <td>{d.user?.cedula || "—"}</td>
                    <td style={{ fontWeight: "bold", color: "#1c6b45" }}>
                      ${d.monto?.toLocaleString() || "0"}
                    </td>
                    <td>{d.moneda || "USD"}</td>
                    <td>{formatearFecha(d.createdAt)}</td>
                    <td>
                      <span style={{ fontWeight: "bold", color: "#f59e0b" }}>
                        {d.puntosOtorgados || "—"}
                      </span>
                    </td>
                    <td>{d.refugio?.nombre || "—"}</td>
                    <td>
                      <span className={d.estado === "COMPLETADO" ? "admin-badge-disponible" : "admin-badge-pendiente-fuerte"}>
                        {d.estado === "COMPLETADO" ? "✅ Completado" : "⏳ Pendiente"}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Donaciones en Especie */}
          <h3 style={{ marginTop: "32px", marginBottom: "12px" }}>📦 Donaciones en Especie</h3>
          <div className="admin-card" style={{ overflowX: "auto" }}>
            <table className="admin-tabla">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Cédula</th>
                  <th>Categoría</th>
                  <th>Especie</th>
                  <th>Cantidad</th>
                  <th>Fecha</th>
                  <th>Puntos</th>
                  <th>Refugio</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {donaciones.especies?.length === 0 && (
                  <tr><td colSpan="10" className="admin-vacio">No hay donaciones en especie</td></tr>
                )}
                {donaciones.especies?.map((d) => (
                  <tr key={d.id}>
                    <td><strong>{d.user?.name || "Usuario eliminado"}</strong></td>
                    <td>{d.user?.cedula || "—"}</td>
                    <td>{d.categoria}</td>
                    <td>{d.especieDestino}</td>
                    <td>{d.cantidad}</td>
                    <td>{formatearFecha(d.createdAt)}</td>
                    <td>
                      <span style={{ fontWeight: "bold", color: "#f59e0b" }}>
                        {d.puntosOtorgados || "—"}
                      </span>
                    </td>
                    <td>{d.refugio?.nombre || "—"}</td>
                    <td>
                      <span className={getEstadoBadge(d.estado)}>{getEstadoLabel(d.estado)}</span>
                    </td>
                    <td>
                      <div className="admin-acciones">
                        {d.estado === "PENDIENTE" && (
                          <>
                            <button 
                              className="admin-icon-btn" 
                              onClick={() => manejarAprobar(d.id)}
                              title="Aprobar"
                            >
                              ✅
                            </button>
                            <button 
                              className="admin-icon-btn peligro" 
                              onClick={() => manejarRechazar(d.id)}
                              title="Rechazar"
                            >
                              ❌
                            </button>
                          </>
                        )}
                        {d.estado === "APROBADO" && (
                          <button 
                            className="admin-icon-btn" 
                            onClick={() => manejarConfirmar(d.id)}
                            title="Confirmar recolección"
                          >
                            📦
                          </button>
                        )}
                        {d.estado === "CONFIRMADO" && (
                          <span style={{ fontSize: "0.8rem", color: "var(--texto-suave)" }}>
                            Completada
                          </span>
                        )}
                        {d.estado === "RECHAZADO" && (
                          <span style={{ fontSize: "0.8rem", color: "var(--texto-suave)" }}>
                            Rechazada
                          </span>
                        )}
                        {d.estado !== "CONFIRMADO" && d.estado !== "RECHAZADO" && (
                          <button 
                            className="admin-icon-btn peligro" 
                            onClick={() => manejarEliminar(d.id)}
                            title="Eliminar"
                          >
                            🗑️
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}

      <ConfirmModal
        abierto={!!confirmando}
        titulo={
          accionConfirmar === "aprobar" ? "¿Aprobar donación?" :
          accionConfirmar === "rechazar" ? "¿Rechazar donación?" :
          accionConfirmar === "confirmar" ? "¿Confirmar recolección?" :
          "¿Eliminar donación?"
        }
        mensaje={
          accionConfirmar === "aprobar" 
            ? "Esta acción aprobará la donación y se podrá agendar la recolección."
            : accionConfirmar === "rechazar"
            ? "Esta acción rechazará la donación. El usuario será notificado."
            : accionConfirmar === "confirmar"
            ? "¿Confirmas que la donación fue recibida? Se otorgarán los puntos al usuario."
            : "Esta acción eliminará la donación permanentemente. ¿Estás seguro?"
        }
        onConfirmar={confirmarAccion}
        onCancelar={() => setConfirmando(null)}
        peligroso={accionConfirmar === "rechazar" || accionConfirmar === "eliminar"}
      />
    </div>
  );
}