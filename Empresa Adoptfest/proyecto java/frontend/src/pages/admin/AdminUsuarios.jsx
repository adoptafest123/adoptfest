import { useEffect, useState } from "react";
import {
  listarUsuarios,
  crearUsuario,
  actualizarUsuario,
  eliminarUsuario,
} from "../../services/adminUserService";
import { useAuth } from "../../context/AuthContext";
import { useToast } from "../../context/ToastContext";
import ConfirmModal from "../../components/ConfirmModal";
import "../../styles/AdminLayout.css";

const FORM_VACIO = { nombre: "", correo: "", cedula: "", telefono: "", contrasena: "", rol: "CLIENTE" };

const soloNumeros = (e) => {
  if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(e.key)) {
    e.preventDefault();
  }
};

export default function AdminUsuarios() {
  const { usuario: yo } = useAuth();
  const toast = useToast();

  const [usuarios, setUsuarios] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [busqueda, setBusqueda] = useState("");

  const [modalAbierto, setModalAbierto] = useState(false);
  const [editandoId, setEditandoId] = useState(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState("");

  const [confirmando, setConfirmando] = useState(null); // usuario a eliminar, o null

  const cargar = (q) => {
    setCargando(true);
    listarUsuarios(q)
      .then((res) => {
        const datos = Array.isArray(res.data)
          ? res.data
          : res.data?.content ?? res.data?.usuarios ?? [];
        setUsuarios(datos);
      })
      .catch(() => toast.error("No se pudieron cargar los usuarios."))
      .finally(() => setCargando(false));
  };

  useEffect(() => cargar(), []);

  useEffect(() => {
    const t = setTimeout(() => cargar(busqueda), 350);
    return () => clearTimeout(t);
  }, [busqueda]);

  const abrirNuevo = () => {
    setEditandoId(null);
    setForm(FORM_VACIO);
    setError("");
    setModalAbierto(true);
  };

  const abrirEditar = (u) => {
    setEditandoId(u.id);
    setForm({ nombre: u.name, correo: u.email, cedula: u.cedula ?? "", telefono: u.telefono ?? "", contrasena: "", rol: u.rol });
    setError("");
    setModalAbierto(true);
  };

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const editandoAMiMismo = editandoId === yo.id;

  const guardar = async (e) => {
    e.preventDefault();
    setGuardando(true);
    setError("");
    try {
      const payload = {
        nombre: form.nombre, correo: form.correo, cedula: form.cedula, telefono: form.telefono, rol: form.rol,
        contrasena: form.contrasena || undefined,
      };
      if (editandoId) {
        await actualizarUsuario(editandoId, payload);
        toast.exito("Usuario actualizado correctamente.");
      } else {
        await crearUsuario(payload);
        toast.exito("Usuario creado correctamente.");
      }
      setModalAbierto(false);
      cargar(busqueda);
    } catch (err) {
      setError(err.response?.data?.mensaje || "No se pudo guardar el usuario.");
    } finally {
      setGuardando(false);
    }
  };

  const pedirEliminar = (u) => {
    if (u.id === yo.id) {
      toast.error("No puedes eliminar tu propia cuenta.");
      return;
    }
    setConfirmando(u);
  };

  const confirmarEliminar = async () => {
    await eliminarUsuario(confirmando.id);
    toast.exito(`${confirmando.name} fue eliminado.`);
    setConfirmando(null);
    cargar(busqueda);
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Usuarios</h1>
          <p>{usuarios.length} cuentas registradas</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <input
            className="admin-buscador"
            placeholder="Buscar por nombre o correo…"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
          />
          <button className="admin-btn-nuevo" onClick={abrirNuevo}>+ Nuevo usuario</button>
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Correo</th>
              <th>Teléfono</th>
              <th>Rol</th>
              <th>Puntos</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="7" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && usuarios.length === 0 && (
              <tr><td colSpan="7" className="admin-vacio">No se encontraron usuarios.</td></tr>
            )}
            {!cargando && usuarios.map((u) => (
              <tr key={u.id}>
                <td><strong>{u.name}</strong> {u.id === yo.id && <span style={{ fontSize: 11, color: "var(--texto-suave)" }}>(tú)</span>}</td>
                <td>{u.cedula ?? "—"}</td>
                <td>{u.email}</td>
                <td>{u.telefono ?? "—"}</td>
                <td>
                  <span className={`admin-badge ${u.rol === "ADMIN" ? "admin-badge-admin" : "admin-badge-cliente"}`}>
                    {u.rol}
                  </span>
                </td>
                <td>{u.puntosDonante ?? 0}</td>
                <td>
                  <div className="admin-acciones">
                    <button className="admin-icon-btn" onClick={() => abrirEditar(u)} title="Editar">✎</button>
                    <button className="admin-icon-btn peligro" onClick={() => pedirEliminar(u)} title="Eliminar">🗑</button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {modalAbierto && (
        <div className="admin-modal-overlay" onClick={() => setModalAbierto(false)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
            <div className="admin-modal-header">
              <h2>{editandoId ? "Editar usuario" : "Nuevo usuario"}</h2>
              <button className="admin-modal-cerrar" onClick={() => setModalAbierto(false)}>✕</button>
            </div>

            <form className="admin-modal-form" onSubmit={guardar}>
              <div className="admin-campo">
                <label>Nombre</label>
                <input name="nombre" value={form.nombre} onChange={handleChange} required />
              </div>

              <div className="admin-campo">
                <label>Correo</label>
                <input type="email" name="correo" value={form.correo} onChange={handleChange} required />
              </div>

              <div className="admin-campo">
                <label>Cédula</label>
                <input name="cedula" value={form.cedula} onChange={handleChange} onKeyDown={soloNumeros} maxLength={15} required />
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Teléfono</label>
                  <input name="telefono" value={form.telefono} onChange={handleChange} onKeyDown={soloNumeros} maxLength={10} required />
                </div>
                <div className="admin-campo">
                  <label>Rol</label>
                  <select
                    name="rol"
                    value={form.rol}
                    onChange={handleChange}
                    disabled={editandoAMiMismo}
                    title={editandoAMiMismo ? "No puedes cambiar tu propio rol" : ""}
                  >
                    <option value="CLIENTE">Cliente</option>
                    <option value="ADMIN">Admin</option>
                  </select>
                  {editandoAMiMismo && (
                    <span style={{ fontSize: 11.5, color: "var(--texto-suave)", display: "block", marginTop: 4 }}>
                      No puedes cambiar tu propio rol.
                    </span>
                  )}
                </div>
              </div>

              <div className="admin-campo">
                <label>{editandoId ? "Nueva contraseña (opcional)" : "Contraseña"}</label>
                <input
                  type="password"
                  name="contrasena"
                  value={form.contrasena}
                  onChange={handleChange}
                  required={!editandoId}
                  placeholder={editandoId ? "Dejar en blanco para no cambiarla" : ""}
                />
              </div>

              {error && <div className="admin-modal-error">{error}</div>}

              <div className="admin-modal-acciones">
                <button type="button" className="admin-btn-cancelar" onClick={() => setModalAbierto(false)}>Cancelar</button>
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
        titulo="¿Eliminar usuario?"
        mensaje={confirmando ? `Esta acción eliminará a "${confirmando.name}" permanentemente. No se puede deshacer.` : ""}
        onConfirmar={confirmarEliminar}
        onCancelar={() => setConfirmando(null)}
      />
    </div>
  );
}