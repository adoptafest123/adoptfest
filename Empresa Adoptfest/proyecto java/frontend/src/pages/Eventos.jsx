import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Hero from "../components/Hero";
import Footer from "../components/Footer";
import { listarEventos } from "../services/eventoService";
import { obtenerCuposEvento } from "../services/eventoService";
import { inscribirseEvento, misInscripciones } from "../services/inscripcionService";
import { obtenerPerfil } from "../services/userService";
import { imagenUrl } from "../services/api";
import { useAuth } from "../context/AuthContext";
import { useToast } from "../context/ToastContext";
import eventoImagen1 from "../assets/imagenes/mascota_3.jpg";
import eventoImagen2 from "../assets/imagenes/mascota_5.jpg";
import "../styles/Eventos.css";

const CATEGORIA_INFO = {
  ADOPCION: { texto: "Adopción", clase: "cat-adopcion", icono: "🐾" },
  EDUCACION: { texto: "Educación", clase: "cat-educacion", icono: "📚" },
  RECREACION: { texto: "Recreación", clase: "cat-recreacion", icono: "🎉" },
};

const soloNumeros = (e) => {
  if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(e.key)) {
    e.preventDefault();
  }
};

function formatearFecha(fechaISO) {
  const f = new Date(fechaISO);
  return f.toLocaleDateString("es-CO", { day: "numeric", month: "long", year: "numeric" });
}

function formatearHora(fechaISO) {
  const f = new Date(fechaISO);
  return f.toLocaleTimeString("es-CO", { hour: "2-digit", minute: "2-digit" });
}

export default function Eventos() {
  const { usuario } = useAuth();
  const navigate = useNavigate();
  const toast = useToast();

  const [eventos, setEventos] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(false);

  const [filtroCategoria, setFiltroCategoria] = useState("todos");
  const [misEventosIds, setMisEventosIds] = useState([]);

  const [eventoActivo, setEventoActivo] = useState(null);

  useEffect(() => {
    listarEventos()
      .then((res) => setEventos(res.data))
      .catch(() => setError(true))
      .finally(() => setCargando(false));

    if (usuario) {
      misInscripciones()
        .then((res) => setMisEventosIds(res.data.map((i) => i.evento?.id)))
        .catch(() => {});
    }
  }, [usuario]);

  const eventosActivos = useMemo(() => eventos.filter((e) => e.estado === "ACTIVO"), [eventos]);

  const eventosFiltrados = useMemo(() => {
    return eventosActivos.filter(
      (e) => filtroCategoria === "todos" || e.categoria === filtroCategoria
    );
  }, [eventosActivos, filtroCategoria]);

  const proximoEvento = useMemo(() => {
    const futuros = [...eventosActivos]
      .filter((e) => new Date(e.fecha) >= new Date())
      .sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    return futuros[0] ?? null;
  }, [eventosActivos]);

  const abrirDetalle = (evento) => setEventoActivo(evento);
  const cerrarDetalle = () => setEventoActivo(null);

  const irAInscribirse = () => {
    if (!usuario) {
      toast.info("Inicia sesión para inscribirte a un evento.");
      navigate("/login");
      return;
    }
  };

  return (
    <>
      <Navbar />

      <Hero
        eyebrow="🎉 Eventos Adoptfest"
        variante="dorado"
        slides={[
          {
            imagen: eventoImagen1,
            titulo: "Actividades que conectan familias con mascotas",
            textoBoton: "Ver eventos",
            linkBoton: "#lista-eventos",
          },
          {
            imagen: eventoImagen2,
            titulo: "Ferias de adopción, jornadas educativas y más",
            textoBoton: "Explorar",
            linkBoton: "#lista-eventos",
          },
        ]}
        franjaInferior={
          <div className="hero-franja-inner">
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#e4f3ea", color: "#1c6b45" }}>🎪</span>
              <div>
                <span className="hero-stat-numero">{eventosActivos.length}</span>
                <span className="hero-stat-label">Eventos activos</span>
              </div>
            </div>
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#fef3c7", color: "#92400e" }}>📅</span>
              <div>
                <span className="hero-stat-numero" style={{ fontSize: 15 }}>
                  {proximoEvento ? formatearFecha(proximoEvento.fecha) : "—"}
                </span>
                <span className="hero-stat-label">Próximo evento</span>
              </div>
            </div>
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#e1eef6", color: "#1c5a85" }}>📍</span>
              <div>
                <span className="hero-stat-numero" style={{ fontSize: 15 }}>
                  {proximoEvento ? proximoEvento.lugar || "Por confirmar" : "—"}
                </span>
                <span className="hero-stat-label">Lugar</span>
              </div>
            </div>
          </div>
        }
      />

      <div className="container eventos-contenido" id="lista-eventos">
        <div className="eventos-filtros">
          {["todos", "ADOPCION", "EDUCACION", "RECREACION"].map((cat) => (
            <button
              key={cat}
              className={`eventos-chip ${filtroCategoria === cat ? "activo" : ""}`}
              onClick={() => setFiltroCategoria(cat)}
            >
              {cat === "todos" ? "Todos" : `${CATEGORIA_INFO[cat].icono} ${CATEGORIA_INFO[cat].texto}`}
            </button>
          ))}
        </div>

        {cargando && (
          <div className="eventos-estado-vacio"><div className="eventos-spinner" /></div>
        )}

        {error && (
          <div className="eventos-estado-vacio"><p>No pudimos cargar los eventos. Intenta recargar la página.</p></div>
        )}

        {!cargando && !error && eventosFiltrados.length === 0 && (
          <div className="eventos-estado-vacio"><p>No hay eventos en esta categoría por ahora. Vuelve pronto 🐾</p></div>
        )}

        {!cargando && !error && eventosFiltrados.length > 0 && (
          <div className="eventos-grid">
            {eventosFiltrados.map((ev) => (
              <EventoCard
                key={ev.id}
                evento={ev}
                yaInscrito={misEventosIds.includes(ev.id)}
                onVerMas={() => abrirDetalle(ev)}
              />
            ))}
          </div>
        )}
      </div>

      {eventoActivo && (
        <EventoDetalleModal
          evento={eventoActivo}
          yaInscrito={misEventosIds.includes(eventoActivo.id)}
          usuario={usuario}
          onCerrar={cerrarDetalle}
          onRequiereLogin={irAInscribirse}
          onInscrito={() => {
            setMisEventosIds((prev) => [...prev, eventoActivo.id]);
            toast.exito(`¡Listo! Te inscribiste a "${eventoActivo.titulo}".`);
            cerrarDetalle();
          }}
        />
      )}

      <Footer />
    </>
  );
}

function EventoCard({ evento, yaInscrito, onVerMas }) {
  const cat = CATEGORIA_INFO[evento.categoria] ?? CATEGORIA_INFO.ADOPCION;
  const [imagenError, setImagenError] = useState(false);

  return (
    <div className="evento-card" onClick={onVerMas}>
      <div className="evento-card-imagen">
        {evento.imagen && !imagenError ? (
          <img 
            src={imagenUrl(evento.imagen)} 
            alt={evento.titulo}
            onError={() => setImagenError(true)}
            style={{ objectFit: "cover" }}
          />
        ) : (
          <div className="evento-card-sin-imagen" style={{ fontSize: "2.5rem" }}>{cat.icono}</div>
        )}
        <span className={`evento-tag ${cat.clase}`}>
          <span className="evento-tag-aro" />
          {cat.texto}
        </span>
        {yaInscrito && <span className="evento-badge-inscrito">✓ Inscrito</span>}
      </div>

      <div className="evento-card-info">
        <span className="evento-card-fecha">{formatearFecha(evento.fecha)} · {formatearHora(evento.fecha)}</span>
        <h3>{evento.titulo}</h3>
        {evento.lugar && <p className="evento-card-lugar">📍 {evento.lugar}</p>}
      </div>
    </div>
  );
}

function EventoDetalleModal({ evento, yaInscrito, usuario, onCerrar, onRequiereLogin, onInscrito }) {
  const cat = CATEGORIA_INFO[evento.categoria] ?? CATEGORIA_INFO.ADOPCION;

  const [mostrandoForm, setMostrandoForm] = useState(false);
  const [cupos, setCupos] = useState(null);
  const [imagenError, setImagenError] = useState(false);
  const [form, setForm] = useState({
    llevaInvitado: false,
    nombreInvitado: "", correoInvitado: "", cedulaInvitado: "", tipoDocumentoInvitado: "CC",
    aceptaReglas: false,
  });
  const [enviando, setEnviando] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    obtenerCuposEvento(evento.id).then((res) => setCupos(res.data)).catch(() => {});
  }, [evento.id]);

  const handleClicInscribirse = () => {
    if (!usuario) {
      onRequiereLogin();
      return;
    }
    setMostrandoForm(true);
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm({ ...form, [name]: type === "checkbox" ? checked : value });
  };

  const esEmailValido = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  const esDocumentoValido = (doc) => /^\d{6,15}$/.test(doc.replace(/\D/g, ""));

  const invitadoValido =
    !form.llevaInvitado ||
    (form.nombreInvitado.trim() &&
      esEmailValido(form.correoInvitado) &&
      esDocumentoValido(form.cedulaInvitado) &&
      form.tipoDocumentoInvitado);

  const formValido = invitadoValido && form.aceptaReglas === true;
  const cuposNecesarios = form.llevaInvitado ? 2 : 1;
  const eventoLleno = cupos?.disponibles != null && cupos.disponibles < cuposNecesarios;

  const confirmarInscripcion = async (e) => {
    e.preventDefault();
    
    // Prevenir envíos duplicados
    if (enviando) return;
    
    setError("");

    // Validar que acepta reglas (OBLIGATORIO)
    if (form.aceptaReglas !== true) {
      setError("❌ Debes aceptar las reglas del evento para continuar.");
      return;
    }

    // Validar si lleva invitado
    if (form.llevaInvitado === true) {
      const nombre = form.nombreInvitado?.trim() || "";
      const correo = form.correoInvitado?.trim() || "";
      const doc = form.cedulaInvitado?.trim() || "";
      const tipoDoc = form.tipoDocumentoInvitado || "";

      if (!nombre) {
        setError("❌ Nombre del invitado es obligatorio.");
        return;
      }
      if (!esEmailValido(correo)) {
        setError(`❌ Correo inválido: "${correo}". Usa formato válido (ej: usuario@gmail.com).`);
        return;
      }
      if (!esDocumentoValido(doc)) {
        setError(`❌ Documento inválido: "${doc}". Debe tener 6-15 dígitos.`);
        return;
      }
      if (!tipoDoc) {
        setError("❌ Selecciona el tipo de documento del invitado.");
        return;
      }
    }

    setEnviando(true);
    try {
      const payload = {
        eventoId: evento.id,
        llevaInvitado: form.llevaInvitado === true,
        nombreInvitado: form.llevaInvitado === true ? form.nombreInvitado.trim() : null,
        correoInvitado: form.llevaInvitado === true ? form.correoInvitado.trim() : null,
        cedulaInvitado: form.llevaInvitado === true ? form.cedulaInvitado.trim() : null,
        tipoDocumentoInvitado: form.llevaInvitado === true ? form.tipoDocumentoInvitado : null,
        aceptaReglas: true,
      };

      console.log("📤 Enviando inscripción:", payload);
      
      const res = await inscribirseEvento(payload);
      console.log("✅ Inscripción exitosa:", res.data);
      onInscrito();
    } catch (err) {
      const msgError = err.response?.data?.mensaje || err.response?.data || "Error desconocido";
      console.error("❌ Error de inscripción completo:", msgError);
      setError(`❌ ${msgError}`);
    } finally {
      setEnviando(false);
    }
  };

  return (
    <div className="evento-modal-overlay" onClick={onCerrar}>
      <div className="evento-modal" onClick={(e) => e.stopPropagation()}>
        <button className="evento-modal-cerrar" onClick={onCerrar}>✕</button>

        <div className="evento-modal-imagen">
          {evento.imagen && !imagenError ? (
            <img 
              src={imagenUrl(evento.imagen)} 
              alt={evento.titulo}
              onError={() => setImagenError(true)}
              style={{ objectFit: "cover" }}
            />
          ) : (
            <div className="evento-card-sin-imagen grande" style={{ fontSize: "4rem" }}>{cat.icono}</div>
          )}
        </div>

        <div className="evento-modal-contenido">
          <span className={`evento-tag ${cat.clase}`}>
            <span className="evento-tag-aro" />
            {cat.texto}
          </span>

          <h2>{evento.titulo}</h2>
          <p className="evento-modal-meta">
            📅 {formatearFecha(evento.fecha)} · {formatearHora(evento.fecha)}
            {evento.horaFin && ` – ${evento.horaFin}`}
            {evento.lugar && <> · 📍 {evento.lugar}</>}
          </p>

          {cupos && cupos.capacidad != null && (
            <p className="evento-cupos-info">
              {eventoLleno ? "🔴 Cupo lleno" : `🎟️ ${cupos.disponibles} cupos disponibles de ${cupos.capacidad}`}
            </p>
          )}

          {evento.descripcion && <p>{evento.descripcion}</p>}

          {yaInscrito && <div className="evento-ya-inscrito">✓ Ya estás inscrito a este evento.</div>}

          {!yaInscrito && eventoLleno && (
            <div className="evento-error" style={{ marginTop: 16 }}>
              No quedan cupos suficientes {form.llevaInvitado && "para ti y tu invitado"}.
            </div>
          )}

          {!yaInscrito && !eventoLleno && !mostrandoForm && (
            <button className="evento-btn-inscribirse" onClick={handleClicInscribirse}>
              Quiero inscribirme 🎟️
            </button>
          )}

          {!yaInscrito && !eventoLleno && mostrandoForm && (
            <form className="evento-form-inscripcion" onSubmit={confirmarInscripcion}>
              <label className="evento-checkbox-linea">
                <input type="checkbox" name="llevaInvitado" checked={form.llevaInvitado} onChange={handleChange} />
                Voy a llevar compañía (un invitado)
              </label>

              {form.llevaInvitado && (
                <div className="evento-tarjeta-invitado">
                  <div className="evento-tarjeta-invitado-header">
                    <span className="evento-tarjeta-invitado-avatar">🧑‍🤝‍🧑</span>
                    <span className="evento-tarjeta-invitado-titulo">Datos de tu invitado</span>
                  </div>

                  <div className="evento-campo">
                    <label>Nombre completo <span style={{ color: "red" }}>*</span></label>
                    <input 
                      type="text"
                      name="nombreInvitado" 
                      value={form.nombreInvitado} 
                      onChange={handleChange} 
                      placeholder="Ej: Juan Pérez"
                    />
                  </div>
                  <div className="evento-campo">
                    <label>Correo <span style={{ color: "red" }}>*</span></label>
                    <input 
                      type="email" 
                      name="correoInvitado" 
                      value={form.correoInvitado} 
                      onChange={handleChange} 
                      placeholder="Ej: juan@gmail.com"
                    />
                  </div>
                  <div className="evento-campos-2">
                    <div className="evento-campo">
                      <label>Tipo de documento <span style={{ color: "red" }}>*</span></label>
                      <select name="tipoDocumentoInvitado" value={form.tipoDocumentoInvitado} onChange={handleChange}>
                        <option value="CC">Cédula de ciudadanía</option>
                        <option value="TI">Tarjeta de identidad</option>
                        <option value="CE">Cédula de extranjería</option>
                        <option value="PAS">Pasaporte</option>
                      </select>
                    </div>
                    <div className="evento-campo">
                      <label>Número de documento <span style={{ color: "red" }}>*</span></label>
                      <input 
                        type="text"
                        name="cedulaInvitado" 
                        value={form.cedulaInvitado} 
                        onChange={handleChange}
                        onKeyDown={soloNumeros}
                        placeholder="Ej: 1234567890"
                        maxLength="15"
                      />
                      {form.cedulaInvitado && !esDocumentoValido(form.cedulaInvitado) && (
                        <span style={{ fontSize: 12, color: "red", marginTop: 4, display: "block" }}>
                          Debe tener 6-15 dígitos
                        </span>
                      )}
                    </div>
                  </div>
                </div>
              )}

              <div className="evento-reglas">
                <p><strong>Reglas del evento:</strong></p>
                <ul>
                  <li>Llega puntual a la hora de inicio.</li>
                  <li>El cupo es personal e intransferible.</li>
                  <li>Si llevas invitado, es tu responsabilidad informarle estas reglas.</li>
                </ul>
                <label className="evento-checkbox-linea">
                  <input type="checkbox" name="aceptaReglas" checked={form.aceptaReglas} onChange={handleChange} />
                  He leído y acepto las reglas del evento
                </label>
              </div>

              {error && <div className="evento-error">{error}</div>}

              <button type="submit" className="evento-btn-inscribirse" disabled={enviando || !formValido}>
                {enviando ? "Enviando…" : "Confirmar inscripción"}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}