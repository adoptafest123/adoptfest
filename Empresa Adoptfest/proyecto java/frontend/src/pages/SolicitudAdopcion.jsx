import { useEffect, useState } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import { obtenerMascota } from "../services/mascotaService";
import { crearSolicitudAdopcion } from "../services/adopcionService";
import { obtenerPerfil } from "../services/userService";
import { useToast } from "../context/ToastContext";
import { imagenUrl } from "../services/api";
import "../styles/SolicitudAdopcion.css";

const FORM_VACIO = {
  nombreCompleto: "", cedula: "", telefono: "", direccion: "", ciudad: "",
  tipoVivienda: "casa", tienePatio: "", esPropia: "",
  tieneNinos: "", edadesNinos: "", tieneOtrosAnimales: "", cualesAnimales: "",
  personasEnCasa: "",
  tieneExperiencia: "", descripcionExperiencia: "", horasSolaMascota: "",
  quienCuidaAusencia: "", quienCuidaDetalle: "", motivoAdopcion: "", compromiso: false,
};

const soloNumeros = (e) => {
  if (!/[0-9]/.test(e.key) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(e.key)) {
    e.preventDefault();
  }
};

const aBool = (v) => v === "si";

// ── Validación por campo: devuelve el mensaje de error, o null si está bien ──
function validarPaso1(f) {
  const errores = {};
  if (f.nombreCompleto.trim().length < 3) errores.nombreCompleto = "Escribe tu nombre completo.";
  if (!/^[0-9]{6,15}$/.test(f.cedula)) errores.cedula = "La cédula debe tener entre 6 y 15 dígitos.";
  if (!/^[0-9]{7,10}$/.test(f.telefono)) errores.telefono = "Ingresa un teléfono válido (7 a 10 dígitos).";
  if (f.direccion.trim().length < 5) errores.direccion = "Escribe una dirección más detallada.";
  if (f.ciudad.trim().length < 2) errores.ciudad = "Escribe tu ciudad.";
  return errores;
}

function validarPaso2(f) {
  const errores = {};
  if (f.esPropia === "") errores.esPropia = "Selecciona una opción.";
  if (f.tienePatio === "") errores.tienePatio = "Selecciona una opción.";
  if (f.tieneNinos === "") errores.tieneNinos = "Selecciona una opción.";
  if (f.tieneNinos === "si" && f.edadesNinos.trim() === "") errores.edadesNinos = "Indica las edades.";
  if (f.tieneOtrosAnimales === "") errores.tieneOtrosAnimales = "Selecciona una opción.";
  if (f.tieneOtrosAnimales === "si" && f.cualesAnimales.trim() === "") errores.cualesAnimales = "Indica cuáles.";
  if (f.personasEnCasa === "" || Number(f.personasEnCasa) < 1 || Number(f.personasEnCasa) > 10) {
    errores.personasEnCasa = "Debe ser un número entre 1 y 10.";
  }
  return errores;
}

function validarPaso3(f) {
  const errores = {};
  if (f.tieneExperiencia === "") errores.tieneExperiencia = "Selecciona una opción.";
  if (f.tieneExperiencia === "si" && f.descripcionExperiencia.trim() === "") {
    errores.descripcionExperiencia = "Describe brevemente tu experiencia.";
  }
  if (f.horasSolaMascota === "" || Number(f.horasSolaMascota) > 24 || Number(f.horasSolaMascota) < 0) {
    errores.horasSolaMascota = "Ingresa un número entre 0 y 24.";
  }
  if (f.quienCuidaAusencia === "") errores.quienCuidaAusencia = "Selecciona quién cuidará a la mascota.";
  if (f.quienCuidaAusencia === "otro" && f.quienCuidaDetalle.trim() === "") {
    errores.quienCuidaDetalle = "Indica quién cuidará a la mascota.";
  }
  if (f.motivoAdopcion.trim().length < 10) errores.motivoAdopcion = `Escribe un poco más (mínimo 10 caracteres, llevas ${f.motivoAdopcion.trim().length}).`;
  if (!f.compromiso) errores.compromiso = "Debes aceptar el compromiso de adopción responsable.";
  return errores;
}

export default function SolicitudAdopcion() {
  const { id } = useParams();
  const navigate = useNavigate();
  const toast = useToast();

  const [mascota, setMascota] = useState(null);
  const [cargando, setCargando] = useState(true);

  const [paso, setPaso] = useState(1);
  const [form, setForm] = useState(FORM_VACIO);
  const [erroresVisibles, setErroresVisibles] = useState({});
  const [enviando, setEnviando] = useState(false);
  const [enviado, setEnviado] = useState(false);
  const [errorGeneral, setErrorGeneral] = useState("");

  useEffect(() => {
    Promise.all([obtenerMascota(id), obtenerPerfil()])
      .then(([resMascota, resPerfil]) => {
        setMascota(resMascota.data);
        setForm((f) => ({
          ...f,
          nombreCompleto: resPerfil.data.name ?? "",
          cedula: resPerfil.data.cedula ?? "",
          telefono: resPerfil.data.telefono ?? "",
        }));
      })
      .catch(() => toast.error("No pudimos cargar la información necesaria."))
      .finally(() => setCargando(false));
  }, [id]);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    const valor = name === "personasEnCasa" && value !== ""
      ? String(Math.min(Number(value), 10))
      : name === "horasSolaMascota" && value !== ""
        ? String(Math.max(0, Math.min(Number(value), 24)))
      : type === "checkbox" ? checked : value;
    setForm((formularioAnterior) => ({ ...formularioAnterior, [name]: valor }));
  };

  const erroresPaso1 = validarPaso1(form);
  const erroresPaso2 = validarPaso2(form);
  const erroresPaso3 = validarPaso3(form);

  const validarYAvanzar = (errores) => {
    if (Object.keys(errores).length > 0) {
      setErroresVisibles(errores);
      return false;
    }
    setErroresVisibles({});
    setPaso((p) => p + 1);
    return true;
  };

  const pasoAnterior = () => {
    setErroresVisibles({});
    setPaso((p) => Math.max(p - 1, 1));
  };

  const enviar = async (e) => {
    e.preventDefault();
    if (Object.keys(erroresPaso3).length > 0) {
      setErroresVisibles(erroresPaso3);
      return;
    }

    setEnviando(true);
    setErrorGeneral("");
    try {
      const payload = {
        mascotaId: Number(id),
        nombreCompleto: form.nombreCompleto,
        cedula: form.cedula,
        telefono: form.telefono,
        direccion: form.direccion,
        ciudad: form.ciudad,
        tipoVivienda: form.tipoVivienda,
        tienePatio: aBool(form.tienePatio),
        esPropia: aBool(form.esPropia),
        tieneNinos: aBool(form.tieneNinos),
        edadesNinos: form.edadesNinos,
        tieneOtrosAnimales: aBool(form.tieneOtrosAnimales),
        cualesAnimales: form.cualesAnimales,
        personasEnCasa: Number(form.personasEnCasa),
        tieneExperiencia: aBool(form.tieneExperiencia),
        descripcionExperiencia: form.descripcionExperiencia,
        horasSolaMascota: Number(form.horasSolaMascota),
        quienCuidaAusencia: form.quienCuidaAusencia === "otro"
          ? `Otro: ${form.quienCuidaDetalle.trim()}`
          : {
              yo: "Yo",
              familiar: "Un familiar",
              pareja: "Mi pareja",
            }[form.quienCuidaAusencia],
        motivoAdopcion: form.motivoAdopcion,
        compromiso: form.compromiso,
      };

      await crearSolicitudAdopcion(payload);
      setEnviado(true);
    } catch (err) {
      setErrorGeneral(err.response?.data?.mensaje || "No se pudo enviar tu solicitud. Intenta de nuevo.");
    } finally {
      setEnviando(false);
    }
  };

  // ── Encabezado minimalista: sin Navbar completo, solo salir/retroceder ──
  const EncabezadoFoco = ({ titulo }) => (
    <header className="sa-topbar">
      <button
        type="button"
        className="sa-topbar-atras"
        onClick={() => (paso > 1 ? pasoAnterior() : navigate(`/adopcion`))}
      >
        ← {paso > 1 ? "Atrás" : "Salir"}
      </button>
      <span className="sa-topbar-titulo">{titulo}</span>
      <Link to="/adopcion" className="sa-topbar-cerrar" title="Cancelar y salir">✕</Link>
    </header>
  );

  if (cargando) {
    return (
      <div className="sa-pagina-foco">
        <EncabezadoFoco titulo="Cargando…" />
        <div className="sa-cargando"><div className="sa-spinner" /></div>
      </div>
    );
  }

  if (!mascota) {
    return (
      <div className="sa-pagina-foco">
        <EncabezadoFoco titulo="No encontrada" />
        <div className="sa-cargando">
          <p>No encontramos esa mascota.</p>
          <Link to="/adopcion" className="sa-btn-primario">Volver al catálogo</Link>
        </div>
      </div>
    );
  }

  if (enviado) {
    return (
      <div className="sa-pagina-foco">
        <EncabezadoFoco titulo="Solicitud enviada" />
        <div className="sa-envolvente">
          <div className="sa-confirmacion">
            <div className="sa-confirmacion-icono">🐾</div>
            <h1>¡Solicitud enviada!</h1>
            <p>
              Recibimos tu solicitud para adoptar a <strong>{mascota.nombre}</strong>.
              Nuestro equipo la va a revisar y te avisaremos por notificación
              cuando tengamos una respuesta.
            </p>
            <div className="sa-confirmacion-acciones">
              <Link to="/adopcion" className="sa-btn-secundario">Ver más mascotas</Link>
              <Link to="/" className="sa-btn-primario">Ir al inicio</Link>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="sa-pagina-foco">
      <EncabezadoFoco titulo={`Adoptar a ${mascota.nombre}`} />

      <div className="sa-envolvente">
        <div className="sa-mascota-resumen">
          {mascota.imagen ? (
            <img src={imagenUrl(mascota.imagen)} alt={mascota.nombre} />
          ) : (
            <div className="sa-mascota-sin-imagen">🐾</div>
          )}
          <div>
            <span className="sa-eyebrow">Solicitud de adopción</span>
            <h1>Adoptar a {mascota.nombre}</h1>
            <p>{mascota.tipo === "perro" ? "🐶" : "🐱"} {mascota.edad} años · Código {mascota.codigo}</p>
          </div>
        </div>

        <div className="sa-pasos-indicador">
          {[1, 2, 3].map((n) => (
            <div key={n} className={`sa-paso-punto ${paso >= n ? "activo" : ""}`}>
              <span>{n}</span>
              <p>{n === 1 ? "Tus datos" : n === 2 ? "Tu hogar" : "Experiencia"}</p>
            </div>
          ))}
        </div>

        <form className="sa-form" onSubmit={enviar}>
          {/* ══════════ PASO 1 ══════════ */}
          {paso === 1 && (
            <div className="sa-seccion">
              <h2>Cuéntanos quién eres</h2>
              <p className="sa-nota-precarga">
                Tu nombre y teléfono se cargan desde tu cuenta y no se pueden editar aquí.
              </p>

              <div className="sa-campo">
                <label>Nombre completo</label>
                <input name="nombreCompleto" value={form.nombreCompleto} readOnly />
                {erroresVisibles.nombreCompleto && <span className="sa-error-campo">{erroresVisibles.nombreCompleto}</span>}
              </div>

              <div className="sa-campos-2">
                <div className="sa-campo">
                  <label>Cédula</label>
                  <input value={form.cedula} disabled className="sa-campo-fijo" />
                  <span className="sa-campo-nota">Dato de tu cuenta — no editable aquí.</span>
                </div>
                <div className="sa-campo">
                  <label>Teléfono</label>
                  <input name="telefono" value={form.telefono} readOnly />
                  {erroresVisibles.telefono && <span className="sa-error-campo">{erroresVisibles.telefono}</span>}
                </div>
              </div>

              <div className="sa-campo">
                <label htmlFor="direccion">Dirección <span aria-hidden="true">*</span></label>
                <input
                  id="direccion"
                  name="direccion"
                  value={form.direccion}
                  onChange={handleChange}
                  required
                  minLength={5}
                  aria-invalid={Boolean(erroresVisibles.direccion)}
                />
                {erroresVisibles.direccion && <span className="sa-error-campo">{erroresVisibles.direccion}</span>}
              </div>

              <div className="sa-campo">
                <label>Ciudad</label>
                <input name="ciudad" value={form.ciudad} onChange={handleChange} />
                {erroresVisibles.ciudad && <span className="sa-error-campo">{erroresVisibles.ciudad}</span>}
              </div>

              <div className="sa-acciones">
                <Link to="/adopcion" className="sa-btn-secundario">Cancelar</Link>
                <button type="button" className="sa-btn-primario" onClick={() => validarYAvanzar(erroresPaso1)}>
                  Siguiente →
                </button>
              </div>
            </div>
          )}

          {/* ══════════ PASO 2 ══════════ */}
          {paso === 2 && (
            <div className="sa-seccion">
              <h2>Cuéntanos sobre tu hogar</h2>

              <div className="sa-campos-2">
                <div className="sa-campo">
                  <label>Tipo de vivienda</label>
                  <select name="tipoVivienda" value={form.tipoVivienda} onChange={handleChange}>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                  </select>
                </div>
                <div className="sa-campo">
                  <label>¿Es propia?</label>
                  <select name="esPropia" value={form.esPropia} onChange={handleChange}>
                    <option value="">Selecciona…</option>
                    <option value="si">Sí</option>
                    <option value="no">Arrendada</option>
                  </select>
                  {erroresVisibles.esPropia && <span className="sa-error-campo">{erroresVisibles.esPropia}</span>}
                </div>
              </div>

              <div className="sa-campo">
                <label>¿Tiene patio o espacio exterior?</label>
                <select name="tienePatio" value={form.tienePatio} onChange={handleChange}>
                  <option value="">Selecciona…</option>
                  <option value="si">Sí</option>
                  <option value="no">No</option>
                </select>
                {erroresVisibles.tienePatio && <span className="sa-error-campo">{erroresVisibles.tienePatio}</span>}
              </div>

              <div className="sa-campos-2">
                <div className="sa-campo">
                  <label>¿Hay niños en casa?</label>
                  <select name="tieneNinos" value={form.tieneNinos} onChange={handleChange}>
                    <option value="">Selecciona…</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                  </select>
                  {erroresVisibles.tieneNinos && <span className="sa-error-campo">{erroresVisibles.tieneNinos}</span>}
                </div>
                {form.tieneNinos === "si" && (
                  <div className="sa-campo">
                    <label>¿Qué Edad tiene El Mayor?</label>
                    <input name="edadesNinos" value={form.edadesNinos} onChange={handleChange} placeholder="Ej: 5 - 8 años" />
                    {erroresVisibles.edadesNinos && <span className="sa-error-campo">{erroresVisibles.edadesNinos}</span>}
                  </div>
                )}
              </div>

              <div className="sa-campos-2">
                <div className="sa-campo">
                  <label>¿Tienes otras mascotas?</label>
                  <select name="tieneOtrosAnimales" value={form.tieneOtrosAnimales} onChange={handleChange}>
                    <option value="">Selecciona…</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                  </select>
                  {erroresVisibles.tieneOtrosAnimales && <span className="sa-error-campo">{erroresVisibles.tieneOtrosAnimales}</span>}
                </div>
                {form.tieneOtrosAnimales === "si" && (
                  <div className="sa-campo">
                    <label>¿Cuantos?</label>
                    <input name="cualesAnimales" value={form.cualesAnimales} onChange={handleChange} placeholder="Ej: 1 perro, 2 gatos" />
                    {erroresVisibles.cualesAnimales && <span className="sa-error-campo">{erroresVisibles.cualesAnimales}</span>}
                  </div>
                )}
              </div>

              <div className="sa-campo">
                <label>¿Cuántas personas viven en casa?</label>
                <input name="personasEnCasa" type="number" min="1" max="10" value={form.personasEnCasa} onChange={handleChange} onKeyDown={soloNumeros} />
                {erroresVisibles.personasEnCasa && <span className="sa-error-campo">{erroresVisibles.personasEnCasa}</span>}
              </div>

              <div className="sa-acciones">
                <button type="button" className="sa-btn-secundario" onClick={pasoAnterior}>← Atrás</button>
                <button type="button" className="sa-btn-primario" onClick={() => validarYAvanzar(erroresPaso2)}>
                  Siguiente →
                </button>
              </div>
            </div>
          )}

          {/* ══════════ PASO 3 ══════════ */}
          {paso === 3 && (
            <div className="sa-seccion">
              <h2>Tu experiencia y compromiso</h2>

              <div className="sa-campo">
                <label>¿Has tenido mascotas antes a tu cuidado?</label>
                <select name="tieneExperiencia" value={form.tieneExperiencia} onChange={handleChange}>
                  <option value="">Selecciona…</option>
                  <option value="si">Sí</option>
                  <option value="no">No, sería mi primera mascota</option>
                </select>
                {erroresVisibles.tieneExperiencia && <span className="sa-error-campo">{erroresVisibles.tieneExperiencia}</span>}
              </div>

              {form.tieneExperiencia === "si" && (
                <div className="sa-campo">
                  <label>Cuéntanos brevemente esa experiencia</label>
                  <textarea
                    name="descripcionExperiencia"
                    rows="2"
                    value={form.descripcionExperiencia}
                    onChange={handleChange}
                    aria-invalid={Boolean(erroresVisibles.descripcionExperiencia)}
                  />
                  {erroresVisibles.descripcionExperiencia && <span className="sa-error-campo">{erroresVisibles.descripcionExperiencia}</span>}
                </div>
              )}

              <div className="sa-campos-2">
                <div className="sa-campo">
                  <label>¿Cuántas horas quedaría sola la mascota al día?</label>
                  <input name="horasSolaMascota" type="number" min="0" max="24" value={form.horasSolaMascota} onChange={handleChange} onKeyDown={soloNumeros} />
                  {erroresVisibles.horasSolaMascota && <span className="sa-error-campo">{erroresVisibles.horasSolaMascota}</span>}
                </div>
                <div className="sa-campo">
                  <label>¿Quién la cuidaría en tu ausencia?</label>
                  <select
                    name="quienCuidaAusencia"
                    value={form.quienCuidaAusencia}
                    onChange={handleChange}
                    aria-invalid={Boolean(erroresVisibles.quienCuidaAusencia)}
                  >
                    <option value="">Selecciona…</option>
                    <option value="yo">Yo</option>
                    <option value="familiar">Un familiar</option>
                    <option value="pareja">Mi pareja</option>
                    <option value="otro">Otro</option>
                  </select>
                  {erroresVisibles.quienCuidaAusencia && <span className="sa-error-campo">{erroresVisibles.quienCuidaAusencia}</span>}
                </div>
              </div>

              {form.quienCuidaAusencia === "otro" && (
                <div className="sa-campo">
                  <label htmlFor="quienCuidaDetalle">¿Quién?</label>
                  <input
                    id="quienCuidaDetalle"
                    name="quienCuidaDetalle"
                    value={form.quienCuidaDetalle}
                    onChange={handleChange}
                    aria-invalid={Boolean(erroresVisibles.quienCuidaDetalle)}
                    placeholder="Escribe quién la cuidaría"
                  />
                  {erroresVisibles.quienCuidaDetalle && <span className="sa-error-campo">{erroresVisibles.quienCuidaDetalle}</span>}
                </div>
              )}

              <div className="sa-campo">
                <label>¿Por qué quieres adoptar a {mascota.nombre}?</label>
                <textarea
                  name="motivoAdopcion"
                  rows="3"
                  value={form.motivoAdopcion}
                  onChange={handleChange}
                  placeholder="Cuéntanos tu motivación (mínimo 10 caracteres)"
                  maxLength={500}
                  aria-invalid={Boolean(erroresVisibles.motivoAdopcion)}
                />
                {erroresVisibles.motivoAdopcion && <span className="sa-error-campo">{erroresVisibles.motivoAdopcion}</span>}
              </div>

              <label className="sa-checkbox">
                <input type="checkbox" name="compromiso" checked={form.compromiso} onChange={handleChange} />
                Me comprometo a brindarle a {mascota.nombre} el cuidado, la alimentación y el
                cariño que necesita durante toda su vida, y entiendo que la adopción es una
                responsabilidad a largo plazo.
              </label>
              {erroresVisibles.compromiso && <span className="sa-error-campo" style={{ display: "block", marginTop: -12, marginBottom: 14 }}>{erroresVisibles.compromiso}</span>}

              {errorGeneral && <div className="sa-error">{errorGeneral}</div>}

              <div className="sa-acciones">
                <button type="button" className="sa-btn-secundario" onClick={pasoAnterior}>← Atrás</button>
                <button type="submit" className="sa-btn-primario" disabled={enviando}>
                  {enviando ? "Enviando…" : "Enviar solicitud 🐾"}
                </button>
              </div>
            </div>
          )}
        </form>
      </div>
    </div>
  );
}