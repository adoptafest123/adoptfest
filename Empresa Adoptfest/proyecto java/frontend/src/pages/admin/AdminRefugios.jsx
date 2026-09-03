// src/pages/admin/AdminRefugios.jsx
import { useEffect, useState } from "react";
import { 
  listarRefugiosTodos,
  obtenerEstadisticasRefugios,
  crearRefugio,
  actualizarRefugio,
  eliminarRefugio
} from "../../services/refugioService";
import { useToast } from "../../context/ToastContext";
import ConfirmModal from "../../components/ConfirmModal";
import "../../styles/AdminLayout.css";

const FORM_VACIO = {
  nombre: "",
  direccion: "",
  telefono: "",
  email: "",
  descripcion: "",
  imagen: "",
  activo: true,
};

export default function AdminRefugios() {
  const toast = useToast();
  const [cargando, setCargando] = useState(true);
  const [refugios, setRefugios] = useState([]);
  const [stats, setStats] = useState({});
  
  const [modalAbierto, setModalAbierto] = useState(false);
  const [editandoId, setEditandoId] = useState(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState("");
  const [confirmando, setConfirmando] = useState(null);

  const cargar = () => {
    setCargando(true);
    Promise.all([
      listarRefugiosTodos(),
      obtenerEstadisticasRefugios()
    ])
      .then(([resRefugios, resStats]) => {
        setRefugios(resRefugios.data);
        setStats(resStats.data);
      })
      .catch(() => toast.error("No se pudieron cargar los refugios."))
      .finally(() => setCargando(false));
  };

  useEffect(() => {
    cargar();
  }, []);

  const abrirNuevo = () => {
    setEditandoId(null);
    setForm(FORM_VACIO);
    setError("");
    setModalAbierto(true);
  };

  const abrirEditar = (refugio) => {
    setEditandoId(refugio.id);
    setForm({
      nombre: refugio.nombre,
      direccion: refugio.direccion,
      telefono: refugio.telefono || "",
      email: refugio.email || "",
      descripcion: refugio.descripcion || "",
      imagen: refugio.imagen || "",
      activo: refugio.activo,
    });
    setError("");
    setModalAbierto(true);
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm({ ...form, [name]: type === "checkbox" ? checked : value });
  };

  const guardar = async (e) => {
    e.preventDefault();
    
    if (!form.nombre.trim() || !form.direccion.trim()) {
      setError("Nombre y dirección son obligatorios.");
      return;
    }

    setGuardando(true);
    setError("");
    try {
      if (editandoId) {
        await actualizarRefugio(editandoId, form);
        toast.exito("Refugio actualizado correctamente.");
      } else {
        await crearRefugio(form);
        toast.exito("Refugio creado correctamente.");
      }
      setModalAbierto(false);
      cargar();
    } catch (err) {
      setError(err.response?.data?.mensaje || "No se pudo guardar el refugio.");
    } finally {
      setGuardando(false);
    }
  };

  const confirmarEliminar = async () => {
    await eliminarRefugio(confirmando.id);
    toast.exito(`${confirmando.nombre} fue eliminado.`);
    setConfirmando(null);
    cargar();
  };

  const formatearMoneda = (monto) => {
    if (!monto) return "$0";
    return "$" + Number(monto).toLocaleString("es-CO");
  };

  const refugiosConStats = refugios.map(refugio => {
    const stat = stats.refugios?.find(s => s.id === refugio.id) || {};
    return { ...refugio, ...stat };
  });

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>🏠 Refugios</h1>
          <p>{refugios.length} refugios registrados</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <button className="admin-btn-nuevo" onClick={cargar}>
            🔄 Refrescar
          </button>
          <button className="admin-btn-nuevo" onClick={abrirNuevo}>
            + Nuevo refugio
          </button>
        </div>
      </div>

      <div className="admin-card" style={{ overflowX: "auto" }}>
        <table className="admin-tabla">
          <thead>
            <tr>
              <th>Refugio</th>
              <th>Dirección</th>
              <th>Contacto</th>
              <th>Donaciones</th>
              <th>Total Recaudado</th>
              <th>Donaciones Especie</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="8" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && refugiosConStats.length === 0 && (
              <tr><td colSpan="8" className="admin-vacio">No hay refugios registrados.</td></tr>
            )}
            {!cargando && refugiosConStats.map((r) => (
              <tr key={r.id}>
                <td><strong>{r.nombre}</strong></td>
                <td style={{ fontSize: "13px" }}>{r.direccion}</td>
                <td style={{ fontSize: "13px" }}>
                  {r.telefono && <div>📞 {r.telefono}</div>}
                  {r.email && <div>✉️ {r.email}</div>}
                </td>
                <td style={{ textAlign: "center", fontWeight: "bold", color: "#1c6b45" }}>
                  {r.totalDonacionesDinero || 0}
                </td>
                <td style={{ textAlign: "center", fontWeight: "bold", color: "#f59e0b" }}>
                  {formatearMoneda(r.totalRecaudadoDinero)}
                </td>
                <td style={{ textAlign: "center", fontWeight: "bold", color: "#3a76ab" }}>
                  {r.totalDonacionesEspecie || 0}
                </td>
                <td>
                  <span className={`admin-badge ${r.activo ? "admin-badge-disponible" : "admin-badge-mal"}`}>
                    {r.activo ? "✅ Activo" : "❌ Inactivo"}
                  </span>
                </td>
                <td>
                  <div className="admin-acciones">
                    <button 
                      className="admin-icon-btn" 
                      onClick={() => abrirEditar(r)} 
                      title="Editar"
                    >
                      ✎
                    </button>
                    <button 
                      className="admin-icon-btn peligro" 
                      onClick={() => setConfirmando(r)} 
                      title="Eliminar"
                    >
                      🗑
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal de crear/editar */}
      {modalAbierto && (
        <div className="admin-modal-overlay" onClick={() => setModalAbierto(false)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
            <div className="admin-modal-header">
              <h2>{editandoId ? "Editar refugio" : "Nuevo refugio"}</h2>
              <button className="admin-modal-cerrar" onClick={() => setModalAbierto(false)}>✕</button>
            </div>

            <form className="admin-modal-form" onSubmit={guardar}>
              <div className="admin-campo">
                <label>Nombre *</label>
                <input name="nombre" value={form.nombre} onChange={handleChange} required />
              </div>

              <div className="admin-campo">
                <label>Dirección *</label>
                <input name="direccion" value={form.direccion} onChange={handleChange} required />
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Teléfono</label>
                  <input name="telefono" value={form.telefono} onChange={handleChange} />
                </div>
                <div className="admin-campo">
                  <label>Email</label>
                  <input type="email" name="email" value={form.email} onChange={handleChange} />
                </div>
              </div>

              <div className="admin-campo">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" value={form.descripcion} onChange={handleChange} />
              </div>

              <div className="admin-campo" style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                <label style={{ margin: 0 }}>Activo</label>
                <input type="checkbox" name="activo" checked={form.activo} onChange={handleChange} />
              </div>

              {error && <div className="admin-modal-error">{error}</div>}

              <div className="admin-modal-acciones">
                <button type="button" className="admin-btn-cancelar" onClick={() => setModalAbierto(false)}>
                  Cancelar
                </button>
                <button type="submit" className="admin-btn-guardar" disabled={guardando}>
                  {guardando ? "Guardando…" : "Guardar"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <ConfirmModal
        abierto={!!confirmando}
        titulo="¿Eliminar refugio?"
        mensaje={confirmando ? `Se eliminará "${confirmando.nombre}" permanentemente.` : ""}
        onConfirmar={confirmarEliminar}
        onCancelar={() => setConfirmando(null)}
      />
    </div>
  );
}