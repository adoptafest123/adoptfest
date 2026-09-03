import { useEffect, useState } from "react";
import { citasPendientes, citasAgendadas, agendarCita } from "../../services/adminCitaService";
import { useToast } from "../../context/ToastContext";
import "../../styles/AdminLayout.css";
import "../../styles/AdminCitas.css";

// Franjas de media hora dentro del horario de atención (9:00 a 17:00)
const HORAS_DISPONIBLES = [];
for (let h = 9; h <= 16; h++) {
  HORAS_DISPONIBLES.push(`${String(h).padStart(2, "0")}:00`);
  HORAS_DISPONIBLES.push(`${String(h).padStart(2, "0")}:30`);
}

function hoyISO() {
  return new Date().toISOString().split("T")[0];
}

export default function AdminCitas() {
  const toast = useToast();
  const [tab, setTab] = useState("pendientes");
  const [pendientes, setPendientes] = useState([]);
  const [agendadas, setAgendadas] = useState([]);
  const [cargando, setCargando] = useState(true);

  const [citaSeleccionada, setCitaSeleccionada] = useState(null);
  const [form, setForm] = useState({ fecha: "", hora: "", notas: "", enlaceVirtual: "" });
  const [error, setError] = useState("");
  const [guardando, setGuardando] = useState(false);

  const cargar = () => {
    setCargando(true);
    Promise.all([citasPendientes(), citasAgendadas()])
      .then(([resPend, resAgen]) => {
        setPendientes(resPend.data);
        setAgendadas(resAgen.data);
      })
      .catch(() => toast.error("No se pudieron cargar las citas."))
      .finally(() => setCargando(false));
  };

  useEffect(cargar, []);

  const abrirAgendar = (cita) => {
    setCitaSeleccionada(cita);
    setForm({ fecha: "", hora: "", notas: "", enlaceVirtual: "" });
    setError("");
  };

  const esDomingo = (fechaStr) => {
    if (!fechaStr) return false;
    const [y, m, d] = fechaStr.split("-").map(Number);
    return new Date(y, m - 1, d).getDay() === 0;
  };

  const formCompleto = Boolean(
    form.fecha &&
    form.hora &&
    form.enlaceVirtual.trim() &&
    form.notas.trim() &&
    !esDomingo(form.fecha)
  );

  const confirmarAgendar = async (e) => {
    e.preventDefault();
    if (!form.fecha || !form.hora || !form.enlaceVirtual.trim() || !form.notas.trim()) {
      setError("Completa fecha, hora, enlace de videollamada y notas antes de confirmar.");
      return;
    }
    if (esDomingo(form.fecha)) {
      setError("No agendamos citas los domingos, elige otro día.");
      return;
    }
    setGuardando(true);
    setError("");
    try {
      await agendarCita(citaSeleccionada.id, form);
      toast.exito(`Cita agendada y notificada a ${citaSeleccionada.user?.name ?? "el usuario"}.`);
      setCitaSeleccionada(null);
      cargar();
    } catch (err) {
      setError(err.response?.data?.mensaje || "No se pudo agendar la cita.");
    } finally {
      setGuardando(false);
    }
  };

  const lista = tab === "pendientes" ? pendientes : agendadas;

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Citas de adopción</h1>
          <p>Citas virtuales · Horario de atención: lunes a sábado, 9:00 a. m. – 5:00 p. m.</p>
        </div>
        <div className="as-tabs">
          <button className={`as-tab ${tab === "pendientes" ? "activo" : ""}`} onClick={() => setTab("pendientes")}>
            Por agendar ({pendientes.length})
          </button>
          <button className={`as-tab ${tab === "agendadas" ? "activo" : ""}`} onClick={() => setTab("agendadas")}>
            Agendadas ({agendadas.length})
          </button>
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
          <thead>
            <tr>
              <th>Mascota</th>
              <th>Adoptante</th>
              {tab === "agendadas" && <><th>Fecha</th><th>Hora</th></>}
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="5" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && lista.length === 0 && (
              <tr><td colSpan="5" className="admin-vacio">
                {tab === "pendientes" ? "No hay citas por agendar." : "Todavía no hay citas agendadas."}
              </td></tr>
            )}
            {!cargando && lista.map((c) => (
              <tr key={c.id}>
                <td><strong>{c.mascota?.nombre}</strong></td>
                <td>{c.user?.name}</td>
                {tab === "agendadas" && <><td>{c.fecha}</td><td>{c.hora}</td></>}
                <td>
                  {tab === "pendientes" && (
                    <button className="admin-btn-nuevo" style={{ padding: "7px 16px", fontSize: 12.5 }} onClick={() => abrirAgendar(c)}>
                      Agendar cita
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {citaSeleccionada && (
        <div className="admin-modal-overlay" onClick={() => setCitaSeleccionada(null)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
            <div className="admin-modal-header">
              <h2>Agendar cita — {citaSeleccionada.mascota?.nombre}</h2>
              <button className="admin-modal-cerrar" onClick={() => setCitaSeleccionada(null)}>✕</button>
            </div>

            <form className="admin-modal-form" onSubmit={confirmarAgendar}>
              <p className="ac-nota">
                Adoptante: <strong>{citaSeleccionada.user?.name}</strong>
              </p>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Fecha</label>
                  <input
                    type="date"
                    min={hoyISO()}
                    value={form.fecha}
                    onChange={(e) => setForm({ ...form, fecha: e.target.value })}
                    required
                  />
                  {esDomingo(form.fecha) && <span className="sa-error-campo">No hay atención los domingos.</span>}
                </div>
                <div className="admin-campo">
                  <label>Hora (horario de atención)</label>
                  <select value={form.hora} onChange={(e) => setForm({ ...form, hora: e.target.value })} required>
                    <option value="">Selecciona…</option>
                    {HORAS_DISPONIBLES.map((h) => (
                      <option key={h} value={h}>{h}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="admin-campo">
                <label>Enlace de la videollamada</label>
                <input
                  type="url"
                  placeholder="https://meet.google.com/..."
                  value={form.enlaceVirtual}
                  onChange={(e) => setForm({ ...form, enlaceVirtual: e.target.value })}
                  required
                />
              </div>

              <div className="admin-campo">
                <label>Notas para el adoptante</label>
                <textarea
                  rows="2"
                  value={form.notas}
                  onChange={(e) => setForm({ ...form, notas: e.target.value })}
                  placeholder="Escribe instrucciones, agenda o recordatorios para la cita."
                  required
                />
              </div>

              {error && <div className="admin-modal-error">{error}</div>}

              <div className="admin-modal-acciones">
                <button type="button" className="admin-btn-cancelar" onClick={() => setCitaSeleccionada(null)}>Cancelar</button>
                <button type="submit" className="admin-btn-guardar" disabled={!formCompleto || guardando}>
                  {guardando ? "Agendando…" : "Confirmar y notificar"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}