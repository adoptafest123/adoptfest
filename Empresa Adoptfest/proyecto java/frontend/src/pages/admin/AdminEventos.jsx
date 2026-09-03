import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import {
  listarEventosAdmin,
  crearEvento,
  actualizarEvento,
  cambiarEstadoEvento,
  eliminarEvento,
} from "../../services/adminEventoService";
import { listarInscripcionesAdmin } from "../../services/adminInscripcionService";
import { subirImagen } from "../../services/uploadService";
import { imagenUrl } from "../../services/api";
import { useToast } from "../../context/ToastContext";
import ConfirmModal from "../../components/ConfirmModal";
import "../../styles/AdminLayout.css";

const CATEGORIA_LABEL = { ADOPCION: "Adopción", EDUCACION: "Educación", RECREACION: "Recreación" };
const ESTADO_LABEL = {
  ACTIVO: { texto: "Activo", clase: "admin-badge-disponible" },
  FINALIZADO: { texto: "Finalizado", clase: "admin-badge-adoptado" },
  CANCELADO: { texto: "Cancelado", clase: "admin-badge-mal" },
};

// Horario fijo para eventos: 8:00 a. m. – 8:00 p. m., cada media hora
const HORAS_EVENTO = [];
for (let h = 8; h <= 20; h++) {
  HORAS_EVENTO.push(`${String(h).padStart(2, "0")}:00`);
  if (h < 20) HORAS_EVENTO.push(`${String(h).padStart(2, "0")}:30`);
}

const FORM_VACIO = {
  titulo: "", fecha: "", horaInicio: "", horaFin: "", lugar: "",
  descripcion: "", categoria: "ADOPCION", capacidad: "", imagen: "",
};

function hoyISO() {
  return new Date().toISOString().split("T")[0];
}

export default function AdminEventos() {
  const toast = useToast();
  const navigate = useNavigate();

  const [eventos, setEventos] = useState([]);
  const [ocupadosPorEvento, setOcupadosPorEvento] = useState({});
  const [cargando, setCargando] = useState(true);
  const [busqueda, setBusqueda] = useState("");

  const [modalAbierto, setModalAbierto] = useState(false);
  const [editandoId, setEditandoId] = useState(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [preview, setPreview] = useState(null);
  const [previewError, setPreviewError] = useState(false);
  const [archivoImagen, setArchivoImagen] = useState(null);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState("");
  const [confirmando, setConfirmando] = useState(null);

  const cargar = async () => {
    setCargando(true);
    try {
      const [resEventos, resInscripciones] = await Promise.all([
        listarEventosAdmin(),
        listarInscripcionesAdmin(),
      ]);
      setEventos(resEventos.data);

      const conteo = {};
      resInscripciones.data.forEach((i) => {
        if (i.estado === "CANCELADA") return;
        const key = i.evento?.id;
        conteo[key] = (conteo[key] || 0) + (i.llevaInvitado ? 2 : 1);
      });
      setOcupadosPorEvento(conteo);
    } catch {
      toast.error("No se pudieron cargar los eventos.");
    } finally {
      setCargando(false);
    }
  };

  useEffect(() => { cargar(); }, []);

  useEffect(() => {
    const t = setTimeout(() => {
      listarEventosAdmin(busqueda).then((res) => setEventos(res.data));
    }, 350);
    return () => clearTimeout(t);
  }, [busqueda]);

  const horaFinValida = form.horaInicio && form.horaFin && form.horaFin > form.horaInicio;
  const fechaValida = form.fecha && form.fecha >= hoyISO();

  const formCompleto =
    form.titulo.trim().length >= 3 &&
    fechaValida && horaFinValida &&
    form.categoria &&
    !!(preview || form.imagen);

  const abrirNuevo = () => {
    setEditandoId(null);
    setForm(FORM_VACIO);
    setPreview(null);
    setPreviewError(false);
    setArchivoImagen(null);
    setError("");
    setModalAbierto(true);
  };

  const abrirEditar = (ev) => {
    const fechaObj = new Date(ev.fecha);
    setEditandoId(ev.id);
    setForm({
      titulo: ev.titulo,
      fecha: fechaObj.toISOString().split("T")[0],
      horaInicio: fechaObj.toTimeString().slice(0, 5),
      horaFin: ev.horaFin ?? "",
      lugar: ev.lugar ?? "",
      descripcion: ev.descripcion ?? "",
      categoria: ev.categoria,
      capacidad: ev.capacidad ?? "",
      imagen: ev.imagen ?? "",
    });
    setPreview(ev.imagen);
    setPreviewError(false);
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
    setPreviewError(false);
  };

  const guardar = async (e) => {
    e.preventDefault();
    if (!formCompleto) {
      setError("Completa título, fecha (no pasada), horario válido, categoría e imagen.");
      return;
    }

    setGuardando(true);
    setError("");
    try {
      let imagenUrl = form.imagen;
      if (archivoImagen) {
        const res = await subirImagen(archivoImagen);
        imagenUrl = res.data.url;
      }

      const payload = {
        titulo: form.titulo,
        fecha: `${form.fecha}T${form.horaInicio}:00`,
        horaFin: `${form.horaFin}:00`,
        lugar: form.lugar,
        descripcion: form.descripcion,
        categoria: form.categoria,
        capacidad: form.capacidad ? Number(form.capacidad) : null,
        imagen: imagenUrl,
      };

      if (editandoId) {
        await actualizarEvento(editandoId, payload);
        toast.exito("Evento actualizado correctamente.");
      } else {
        await crearEvento(payload);
        toast.exito("Evento creado correctamente.");
      }

      setModalAbierto(false);
      cargar();
    } catch (err) {
      setError(err.response?.data?.mensaje || "No se pudo guardar el evento.");
    } finally {
      setGuardando(false);
    }
  };

  const cambiarEstado = async (evento, nuevoEstado) => {
    await cambiarEstadoEvento(evento.id, nuevoEstado);
    toast.info(`"${evento.titulo}" ahora está ${ESTADO_LABEL[nuevoEstado].texto.toLowerCase()}.`);
    cargar();
  };

  const confirmarEliminar = async () => {
    await eliminarEvento(confirmando.id);
    toast.exito(`"${confirmando.titulo}" fue eliminado.`);
    setConfirmando(null);
    cargar();
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Eventos</h1>
          <p>{eventos.length} registrados en total</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <input
            className="admin-buscador"
            placeholder="Buscar por título o lugar…"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
          />
          <button className="admin-btn-nuevo" onClick={abrirNuevo}>+ Nuevo evento</button>
        </div>
      </div>

      <div className="admin-card">
        <table className="admin-tabla">
          <thead>
            <tr>
              <th></th>
              <th>Título</th>
              <th>Fecha</th>
              <th>Categoría</th>
              <th>Cupos</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="7" className="admin-vacio">Cargando…</td></tr>}
            {!cargando && eventos.length === 0 && (
              <tr><td colSpan="7" className="admin-vacio">No hay eventos registrados.</td></tr>
            )}
            {!cargando && eventos.map((ev) => {
              const estado = ESTADO_LABEL[ev.estado] ?? ESTADO_LABEL.ACTIVO;
              const ocupados = ocupadosPorEvento[ev.id] || 0;
              const lleno = ev.capacidad != null && ocupados >= ev.capacidad;
              return (
                <tr key={ev.id}>
                  <td>
                    {ev.imagen ? (
                      <img src={imagenUrl(ev.imagen)} className="admin-tabla-avatar" alt={ev.titulo} />
                    ) : (
                      <div className="admin-tabla-avatar" style={{ display: "flex", alignItems: "center", justifyContent: "center" }}>🎉</div>
                    )}
                  </td>
                  <td><strong>{ev.titulo}</strong></td>
                  <td>{new Date(ev.fecha).toLocaleString("es-CO", { day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit" })}</td>
                  <td>{CATEGORIA_LABEL[ev.categoria]}</td>
                  <td>
                    <span className={`admin-badge ${lleno ? "admin-badge-mal" : "admin-badge-disponible"}`}>
                      {ocupados}/{ev.capacidad ?? "∞"}
                    </span>
                  </td>
                  <td>
                    <select className="admin-badge-select" value={ev.estado} onChange={(e) => cambiarEstado(ev, e.target.value)}>
                      <option value="ACTIVO">Activo</option>
                      <option value="FINALIZADO">Finalizado</option>
                      <option value="CANCELADO">Cancelado</option>
                    </select>
                  </td>
                  <td>
                    <div className="admin-acciones">
                      <button className="admin-icon-btn" onClick={() => navigate(`/admin/inscripciones?eventoId=${ev.id}`)} title="Ver inscritos">👥</button>
                      <button className="admin-icon-btn" onClick={() => abrirEditar(ev)} title="Editar">✎</button>
                      <button className="admin-icon-btn peligro" onClick={() => setConfirmando(ev)} title="Eliminar">🗑</button>
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
              <h2>{editandoId ? "Editar evento" : "Nuevo evento"}</h2>
              <button className="admin-modal-cerrar" onClick={() => setModalAbierto(false)}>✕</button>
            </div>

            <form className="admin-modal-form" onSubmit={guardar}>
              <div style={{ textAlign: "center" }}>
                <div style={{
                  width: 100, height: 60, borderRadius: 12, background: "var(--color-verde-suave)",
                  margin: "0 auto 10px", overflow: "hidden", display: "flex", alignItems: "center", justifyContent: "center",
                }}>
                  {preview && !previewError ? (
                    <img 
                      src={imagenUrl(preview)} 
                      alt="" 
                      style={{ width: "100%", height: "100%", objectFit: "cover" }}
                      onError={() => setPreviewError(true)}
                    /> 
                  ) : (
                    <span style={{ fontSize: "1.5rem" }}>🎉</span>
                  )}
                </div>
                <label className="admin-btn-cancelar" style={{ cursor: "pointer", display: "inline-block" }}>
                  Elegir imagen {!preview && <span style={{ color: "#b3392b" }}>*</span>}
                  <input type="file" accept="image/*" hidden onChange={handleImagen} />
                </label>
                {preview && <p style={{ fontSize: 12, color: "var(--texto-suave)", marginTop: 5 }}>
                  {previewError ? "⚠️ No se puede cargar la vista previa" : "✓ Imagen seleccionada"}
                </p>}
              </div>

              <div className="admin-campo">
                <label>Título *</label>
                <input name="titulo" value={form.titulo} onChange={handleChange} required minLength={3} />
              </div>

              <div className="admin-campo">
                <label>Fecha * (no puede ser pasada)</label>
                <input name="fecha" type="date" min={hoyISO()} value={form.fecha} onChange={handleChange} required />
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Hora de inicio *</label>
                  <select name="horaInicio" value={form.horaInicio} onChange={handleChange} required>
                    <option value="">Selecciona…</option>
                    {HORAS_EVENTO.map((h) => <option key={h} value={h}>{h}</option>)}
                  </select>
                </div>
                <div className="admin-campo">
                  <label>Hora de cierre *</label>
                  <select name="horaFin" value={form.horaFin} onChange={handleChange} required>
                    <option value="">Selecciona…</option>
                    {HORAS_EVENTO.map((h) => <option key={h} value={h}>{h}</option>)}
                  </select>
                  {form.horaInicio && form.horaFin && !horaFinValida && (
                    <span className="sa-error-campo">Debe ser posterior a la hora de inicio.</span>
                  )}
                </div>
              </div>

              <div className="admin-campos-2">
                <div className="admin-campo">
                  <label>Lugar</label>
                  <input name="lugar" value={form.lugar} onChange={handleChange} />
                </div>
                <div className="admin-campo">
                  <label>Capacidad (opcional, vacío = sin límite)</label>
                  <input name="capacidad" type="number" min="1" value={form.capacidad} onChange={handleChange} />
                </div>
              </div>

              <div className="admin-campo">
                <label>Categoría *</label>
                <select name="categoria" value={form.categoria} onChange={handleChange} required>
                  <option value="ADOPCION">Adopción</option>
                  <option value="EDUCACION">Educación</option>
                  <option value="RECREACION">Recreación</option>
                </select>
              </div>

              <div className="admin-campo">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" value={form.descripcion} onChange={handleChange} />
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
        titulo="¿Eliminar evento?"
        mensaje={confirmando ? `Se eliminará "${confirmando.titulo}" permanentemente.` : ""}
        onConfirmar={confirmarEliminar}
        onCancelar={() => setConfirmando(null)}
      />
    </div>
  );
}