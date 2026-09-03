import { useEffect, useMemo, useState } from "react";
import {
  listarMascotasAdmin,
  crearMascota,
  actualizarMascota,
  eliminarMascota,
} from "../../services/adminMascotaService";
import { subirImagen } from "../../services/uploadService";
import { imagenUrl } from "../../services/api";
import { useToast } from "../../context/ToastContext";
import ConfirmModal from "../../components/ConfirmModal";
import "../../styles/AdminLayout.css";

const ESTADO_LABEL = {
  DISPONIBLE: { texto: "Disponible", clase: "admin-badge-disponible" },
  EN_EVENTO: { texto: "En evento", clase: "admin-badge-evento" },
  PROCESO: { texto: "En proceso", clase: "admin-badge-proceso" },
  ADOPTADO: { texto: "Adoptado", clase: "admin-badge-adoptado" },
};

const FORM_VACIO = {
  nombre: "", tipo: "perro", edad: "", raza: "", peso: "", estatura: "",
  descripcion: "", estado: "DISPONIBLE", imagen: "", vacunado: "", esterilizado: "",
};

export default function AdminMascotas() {
  const toast = useToast();
  const [mascotas, setMascotas] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [busqueda, setBusqueda] = useState("");

  const [modalAbierto, setModalAbierto] = useState(false);
  const [editandoId, setEditandoId] = useState(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [archivoImagen, setArchivoImagen] = useState(null);
  const [preview, setPreview] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState("");

  const [confirmando, setConfirmando] = useState(null);

  const cargar = () => {
    setCargando(true);
    listarMascotasAdmin()
      .then((res) => {
        const datos = Array.isArray(res.data)
          ? res.data
          : res.data?.content ?? res.data?.mascotas ?? [];
        setMascotas(datos);
      })
      .catch(() => toast.error("No se pudieron cargar las mascotas."))
      .finally(() => setCargando(false));
  };

  useEffect(cargar, []);

  const filtradas = mascotas.filter((m) => {
    const textoBusqueda = busqueda.toLowerCase();
    return m.nombre.toLowerCase().includes(textoBusqueda)
      || (m.codigo ?? "").toLowerCase().includes(textoBusqueda);
  });

  // ── Validación real: el botón Guardar solo se habilita si TODO está lleno ──
  const formCompleto = useMemo(() => {
  const tieneImagen = !!(preview || form.imagen);
  return (
    form.nombre.trim().length >= 2 &&
    form.tipo &&
    form.edad !== "" && Number(form.edad) >= 1 && Number(form.edad) <= 10 &&
    form.raza.trim().length >= 2 &&
    form.peso !== "" && Number(form.peso) > 0 &&
    form.estatura.trim().length >= 1 &&
    form.descripcion.trim().length >= 10 &&
    form.estado &&
    tieneImagen &&
    form.vacunado !== "" &&
    form.esterilizado !== ""
  );
}, [form, preview]);

  const abrirNuevo = () => {
    setEditandoId(null);
    setForm(FORM_VACIO);
    setPreview(null);
    setArchivoImagen(null);
    setError("");
    setModalAbierto(true);
  };

const abrirEditar = (m) => {
  setEditandoId(m.id);
  setForm({
    nombre: m.nombre, tipo: m.tipo, edad: m.edad ?? "", raza: m.raza ?? "",
    peso: m.peso ?? "", estatura: m.estatura ?? "", descripcion: m.descripcion ?? "",
    estado: m.estado, imagen: m.imagen ?? "",
    vacunado: m.vacunado ? "si" : "no", esterilizado: m.esterilizado ? "si" : "no",
  });
  setPreview(m.imagen);
  setArchivoImagen(null);
  setError("");
  setModalAbierto(true);
};

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleImagen = (e) => {
    const archivo = e.target.files[0];
    if (!archivo) return;
    setArchivoImagen(archivo);
    setPreview(URL.createObjectURL(archivo));
  };

  const guardar = async (e) => {
    e.preventDefault();
    if (!formCompleto) {
      setError("Completa todos los campos, incluyendo la foto, antes de guardar.");
      return;
    }

    setGuardando(true);
    setError("");
    try {
      let imagenUrl = form.imagen;

      // Solo sube una imagen nueva si el admin eligió un archivo distinto
      if (archivoImagen) {
        const res = await subirImagen(archivoImagen);
        imagenUrl = res.data.url;
      }

      if (!imagenUrl) {
        setError("Debes subir una foto de la mascota.");
        setGuardando(false);
        return;
      }

    const payload = {
  ...form,
  edad: Number(form.edad),
  peso: Number(form.peso),
  imagen: imagenUrl,
  vacunado: form.vacunado === "si",
  esterilizado: form.esterilizado === "si",
};
      if (editandoId) {
        await actualizarMascota(editandoId, payload);
        toast.exito("Mascota actualizada correctamente.");
      } else {
        await crearMascota(payload);
        toast.exito("Mascota creada correctamente.");
      }

      setModalAbierto(false);
      cargar();
    } catch (err) {
      setError(err.response?.data?.mensaje || "No se pudo guardar la mascota. Revisa los campos.");
    } finally {
      setGuardando(false);
    }
  };

  const confirmarEliminar = async () => {
    await eliminarMascota(confirmando.id);
    toast.exito(`${confirmando.nombre} fue eliminada.`);
    setConfirmando(null);
    cargar();
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Mascotas</h1>
          <p>{mascotas.length} registradas en total</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <input
            className="admin-buscador"
            placeholder="Buscar por nombre o código…"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
          />
          <button className="admin-btn-nuevo" onClick={abrirNuevo}>+ Nueva mascota</button>
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
         <thead>
  <tr>
    <th></th>
    <th>Nombre</th>
    <th>Tipo</th>
    <th>Raza</th>
    <th>Edad</th>
    <th>Peso</th>
    <th>Salud</th>
    <th>Estado</th>
    <th>Código</th>
    <th></th>
  </tr>
</thead>
         <tbody>
  {cargando && <tr><td colSpan="9" className="admin-vacio">Cargando…</td></tr>}
  {!cargando && filtradas.length === 0 && (
    <tr><td colSpan="9" className="admin-vacio">No hay mascotas registradas.</td></tr>
  )}
  {!cargando && filtradas.map((m) => {
    const estado = ESTADO_LABEL[m.estado] ?? ESTADO_LABEL.DISPONIBLE;
    return (
      <tr key={m.id}>
        <td>
          {m.imagen ? (
            <img src={imagenUrl(m.imagen)} className="admin-tabla-avatar" alt={m.nombre} />
          ) : (
            <div className="admin-tabla-avatar" style={{ display: "flex", alignItems: "center", justifyContent: "center" }}>🐾</div>
          )}
        </td>
        <td><strong>{m.nombre}</strong></td>
        <td style={{ textTransform: "capitalize" }}>{m.tipo}</td>
        <td>{m.raza || "—"}</td>
        <td>{m.edad} años</td>
        <td>{m.peso ? `${m.peso} kg` : "—"}</td>
        <td style={{ fontSize: 16 }}>
          <span title="Vacunado">{m.vacunado ? "💉" : "—"}</span>{" "}
          <span title="Esterilizado">{m.esterilizado ? "✂️" : "—"}</span>
        </td>
        <td><span className={`admin-badge ${estado.clase}`}>{estado.texto}</span></td>
        <td>{m.codigo}</td>
        <td>
          <div className="admin-acciones">
            <button className="admin-icon-btn" onClick={() => abrirEditar(m)} title="Editar">✎</button>
            <button className="admin-icon-btn peligro" onClick={() => setConfirmando(m)} title="Eliminar">🗑</button>
          </div>
        </td>
      </tr>
    );
  })}
</tbody>
        </table>
      </div>

      {modalAbierto && (
        <div className="admin-modal-overlay" onClick={() => setModalAbierto(false)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
            <div className="admin-modal-header">
              <h2>{editandoId ? "Editar mascota" : "Nueva mascota"}</h2>
              <button className="admin-modal-cerrar" onClick={() => setModalAbierto(false)}>✕</button>
            </div>

            <form className="admin-modal-form" onSubmit={guardar}>
              <div style={{ textAlign: "center" }}>
                <div style={{
                  width: 80, height: 80, borderRadius: 16, background: "var(--color-verde-suave)",
                  margin: "0 auto 10px", overflow: "hidden", display: "flex", alignItems: "center", justifyContent: "center",
                }}>
                  {preview ? <img src={imagenUrl(preview)} alt="" style={{ width: "100%", height: "100%", objectFit: "cover" }} /> : "🐾"}
                </div>
                <label className="admin-btn-cancelar" style={{ cursor: "pointer", display: "inline-block" }}>
                  Elegir foto {!preview && <span style={{ color: "#b3392b" }}>*</span>}
                  <input type="file" accept="image/*" hidden onChange={handleImagen} />
                </label>
              </div>

              <div className="admin-campo">
                <label>Nombre *</label>
                <input name="nombre" value={form.nombre} onChange={handleChange} required minLength={2} />
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Tipo *</label>
                  <select name="tipo" value={form.tipo} onChange={handleChange} required>
                    <option value="perro">Perro</option>
                    <option value="gato">Gato</option>
                  </select>
                </div>
                <div className="admin-campo">
                  <label>Edad (1 a 10 años) *</label>
                  <input name="edad" type="number" min="1" max="10" value={form.edad} onChange={handleChange} required />
                </div>
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>¿Vacunado? *</label>
                  <select name="vacunado" value={form.vacunado} onChange={handleChange} required>
                    <option value="">Selecciona…</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                  </select>
                </div>
                <div className="admin-campo">
                  <label>¿Esterilizado? *</label>
                  <select name="esterilizado" value={form.esterilizado} onChange={handleChange} required>
                    <option value="">Selecciona…</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                  </select>
                </div>
              </div>

              <div className="admin-campo">
                <label>Estado *</label>
                <select name="estado" value={form.estado} onChange={handleChange} required>
                  <option value="DISPONIBLE">Disponible</option>
                  <option value="EN_EVENTO">En evento</option>
                  <option value="PROCESO">En proceso</option>
                  <option value="ADOPTADO">Adoptado</option>
                </select>
              </div>

              <div className="admin-campo">
                <label>Descripción corta * (mín. 10 caracteres)</label>
                <textarea name="descripcion" rows="2" value={form.descripcion} onChange={handleChange} required minLength={10} />
              </div>

              <div className="admin-campos-2">
  <div className="admin-campo">
    <label>Raza *</label>
    <input name="raza" value={form.raza} onChange={handleChange} required minLength={2} placeholder="Ej: Criollo, Labrador..." />
  </div>
  <div className="admin-campo">
    <label>Peso (kg) *</label>
    <input name="peso" type="number" step="0.1" min="0.1" value={form.peso} onChange={handleChange} required />
  </div>
</div>

<div className="admin-campo">
  <label>Estatura *</label>
  <input name="estatura" value={form.estatura} onChange={handleChange} required placeholder="Ej: 35 cm" />
</div>

              {error && <div className="admin-modal-error">{error}</div>}

              <div className="admin-modal-acciones">
                <button type="button" className="admin-btn-cancelar" onClick={() => setModalAbierto(false)}>Cancelar</button>
                <button type="submit" className="admin-btn-guardar" disabled={guardando || !formCompleto}>
                  {guardando ? "Guardando…" : "Guardar"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <ConfirmModal
        abierto={!!confirmando}
        titulo="¿Eliminar mascota?"
        mensaje={confirmando ? `Se eliminará a "${confirmando.nombre}" permanentemente, junto con su foto.` : ""}
        onConfirmar={confirmarEliminar}
        onCancelar={() => setConfirmando(null)}
      />
    </div>
  );
}