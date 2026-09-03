import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Hero from "../components/Hero";
import Footer from "../components/Footer";
import { interesMascota, yaSolicitéMascota } from "../services/adopcionService";
import { listarMascotas } from "../services/mascotaService";
import { useAuth } from "../context/AuthContext";
import mascotaAdopcion1 from "../assets/imagenes/mascota_1.jpg";
import mascotaAdopcion2 from "../assets/imagenes/mascota_3.jpg";
import "../styles/Adopcion.css";
import { imagenUrl } from "../services/api";

const ESTADO_INFO = {
  DISPONIBLE: { texto: "Disponible", clase: "estado-disponible" },
  EN_EVENTO: { texto: "En un evento", clase: "estado-evento" },
  PROCESO: { texto: "En proceso", clase: "estado-proceso" },
  ADOPTADO: { texto: "Adoptado", clase: "estado-adoptado" },
};

function Adopcion() {
  const { usuario } = useAuth();
  const [mascotas, setMascotas] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(false);

  const [busqueda, setBusqueda] = useState("");
  const [filtroTipo, setFiltroTipo] = useState("todos");
  const [soloDisponibles, setSoloDisponibles] = useState(false);

  const [mascotaActiva, setMascotaActiva] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    listarMascotas()
      .then((res) => setMascotas(res.data))
      .catch(() => setError(true))
      .finally(() => setCargando(false));
  }, []);

  const mascotasFiltradas = useMemo(() => {
    return mascotas.filter((m) => {
      const textoBusqueda = busqueda.toLowerCase();
      const coincideBusqueda = m.nombre.toLowerCase().includes(textoBusqueda)
        || (m.codigo ?? "").toLowerCase().includes(textoBusqueda);
      const coincideTipo = filtroTipo === "todos" || m.tipo === filtroTipo;
      const coincideDisponibilidad = !soloDisponibles || m.estado === "DISPONIBLE";
      return coincideBusqueda && coincideTipo && coincideDisponibilidad;
    });
  }, [mascotas, busqueda, filtroTipo, soloDisponibles]);

  const totalDisponibles = useMemo(
    () => mascotas.filter((m) => m.estado === "DISPONIBLE").length,
    [mascotas]
  );
  const totalPerros = useMemo(
    () => mascotas.filter((m) => m.tipo === "perro" && m.estado === "DISPONIBLE").length,
    [mascotas]
  );
  const totalGatos = useMemo(
    () => mascotas.filter((m) => m.tipo === "gato" && m.estado === "DISPONIBLE").length,
    [mascotas]
  );

  const irASolicitud = (mascota) => {
    setMascotaActiva(null);
    navigate(`/adopcion/${mascota.id}/solicitud`);
  };

  return (
    <>
      <Navbar />

      <Hero
        eyebrow="🐾 Catálogo de adopción"
        variante="coral"
        slides={[
          {
            imagen: mascotaAdopcion1,
            titulo: "Adoptar es el primer paso hacia una nueva historia",
            textoBoton: "Ver catálogo",
            linkBoton: "#catalogo",
          },
          {
            imagen: mascotaAdopcion2,
            titulo: "Cada mascota merece una segunda oportunidad",
            textoBoton: "Conocer el proceso",
            linkBoton: "#catalogo",
          },
        ]}
        franjaInferior={
          <div className="hero-franja-inner">
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#e4f3ea", color: "#1c6b45" }}>
                🏠
              </span>
              <div>
                <span className="hero-stat-numero">{totalDisponibles}</span>
                <span className="hero-stat-label">Disponibles</span>
              </div>
            </div>
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#fdf1de", color: "#a3690c" }}>
                🐶
              </span>
              <div>
                <span className="hero-stat-numero">{totalPerros}</span>
                <span className="hero-stat-label">Perros</span>
              </div>
            </div>
            <div className="hero-stat">
              <span className="hero-stat-icono" style={{ background: "#e1eef6", color: "#1c5a85" }}>
                🐱
              </span>
              <div>
                <span className="hero-stat-numero">{totalGatos}</span>
                <span className="hero-stat-label">Gatos</span>
              </div>
            </div>
          </div>
        }
      />

      <div className="container adopcion-contenido" id="catalogo">
        <div className="adopcion-filtros">
          <input
            type="text"
            placeholder="Buscar por nombre o código…"
            className="adopcion-buscador"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
          />

          <div className="adopcion-chips">
            {[
              { id: "todos", label: "Todos", icono: "🐾" },
              { id: "perro", label: "Perros", icono: "🐶" },
              { id: "gato", label: "Gatos", icono: "🐱" },
            ].map((t) => (
              <button
                key={t.id}
                className={`adopcion-chip ${filtroTipo === t.id ? "activo" : ""}`}
                onClick={() => setFiltroTipo(t.id)}
              >
                {t.icono} {t.label}
              </button>
            ))}
          </div>

          <label className="adopcion-toggle">
            <input
              type="checkbox"
              checked={soloDisponibles}
              onChange={(e) => setSoloDisponibles(e.target.checked)}
            />
            <span className="adopcion-toggle-slider" />
            Solo disponibles
          </label>
        </div>

        {cargando && (
          <div className="adopcion-estado-vacio">
            <div className="adopcion-spinner" />
          </div>
        )}

        {error && (
          <div className="adopcion-estado-vacio">
            <p>No pudimos cargar el catálogo. Intenta recargar la página.</p>
          </div>
        )}

        {!cargando && !error && mascotasFiltradas.length === 0 && (
          <div className="adopcion-estado-vacio">
            <p>No encontramos mascotas con esos filtros.</p>
          </div>
        )}

        {!cargando && !error && mascotasFiltradas.length > 0 && (
          <div className="adopcion-grid">
            {mascotasFiltradas.map((m) => (
              <MascotaCard key={m.id} mascota={m} onVerMas={() => setMascotaActiva(m)} />
            ))}
          </div>
        )}
      </div>

      {mascotaActiva && (
        <MascotaDetalleModal
          mascota={mascotaActiva}
          usuario={usuario}
          onCerrar={() => setMascotaActiva(null)}
          onAdoptar={() => irASolicitud(mascotaActiva)}
        />
      )}

      <Footer />
    </>
  );
}

function MascotaCard({ mascota, onVerMas }) {
  const disponible = mascota.estado === "DISPONIBLE";
  const estado = ESTADO_INFO[mascota.estado] ?? ESTADO_INFO.DISPONIBLE;

  return (
    <div className="mascota-card" onClick={onVerMas}>
      <div className="mascota-card-imagen">
        {mascota.imagen ? (
          <img src={imagenUrl(mascota.imagen)} alt={mascota.nombre} />
        ) : (
          <div className="mascota-card-sin-imagen">🐾</div>
        )}
        {!disponible && (
          <span className={`mascota-tag ${estado.clase}`}>
            <span className="mascota-tag-aro" />
            {estado.texto}
          </span>
        )}
      </div>

      <div className="mascota-card-info">
        <div className="mascota-card-top">
          <h3>{mascota.nombre}</h3>
          {disponible && <span className="mascota-boton-disponible">🟢 Disponible</span>}
        </div>

        <div className="mascota-card-datos">
          <span>{mascota.tipo === "perro" ? "🐶" : "🐱"} {mascota.edad} {mascota.edad === 1 ? "año" : "años"}</span>
          {mascota.raza && <span>🏷️ {mascota.raza}</span>}
          {mascota.peso && <span>⚖️ {mascota.peso} kg</span>}
          {mascota.estatura && <span>📏 {mascota.estatura}</span>}
        </div>
      </div>
    </div>
  );
}

function MascotaDetalleModal({ mascota, usuario, onCerrar, onAdoptar }) {
  const estado = ESTADO_INFO[mascota.estado] ?? ESTADO_INFO.DISPONIBLE;
  const disponible = mascota.estado === "DISPONIBLE";

  const [solicitudesPendientes, setSolicitudesPendientes] = useState(0);
  const [yaSolicite, setYaSolicite] = useState(false);
  const [cargandoEstado, setCargandoEstado] = useState(true);

  useEffect(() => {
    Promise.all([
      interesMascota(mascota.id),
      usuario ? yaSolicitéMascota(mascota.id) : Promise.resolve({ data: { yaSolicite: false } }),
    ])
      .then(([resInteres, resYaSolicite]) => {
        setSolicitudesPendientes(resInteres.data.solicitudesPendientes ?? 0);
        setYaSolicite(resYaSolicite.data.yaSolicite ?? false);
      })
      .catch(() => {})
      .finally(() => setCargandoEstado(false));
  }, [mascota.id, usuario]);

  const puedeAdoptar = disponible && !yaSolicite;

  return (
    <div className="mascota-modal-overlay" onClick={onCerrar}>
      <div className="mascota-modal" onClick={(e) => e.stopPropagation()}>
        <button className="mascota-modal-cerrar" onClick={onCerrar}>✕</button>

        <div className="mascota-modal-imagen">
          {mascota.imagen ? (
            <img src={imagenUrl(mascota.imagen)} alt={mascota.nombre} />
          ) : (
            <div className="mascota-card-sin-imagen grande">🐾</div>
          )}
        </div>

        <div className="mascota-modal-contenido">
          {disponible ? (
            <span className="mascota-boton-disponible mascota-boton-disponible-grande">🟢 Disponible ahora</span>
          ) : (
            <span className={`mascota-tag ${estado.clase}`}>
              <span className="mascota-tag-aro" />
              {estado.texto}
            </span>
          )}

          <h2>{mascota.nombre}</h2>
          <p className="mascota-modal-meta">
            {mascota.tipo === "perro" ? "🐶 Perro" : "🐱 Gato"} · Código {mascota.codigo}
          </p>

          {disponible && !cargandoEstado && solicitudesPendientes > 0 && (
            <div className="mascota-aviso-interes">
              🔥 {solicitudesPendientes} {solicitudesPendientes === 1 ? "persona ya solicitó" : "personas ya solicitaron"} adoptar a {mascota.nombre}. ¡Aún puedes enviar tu solicitud!
            </div>
          )}

          {yaSolicite && (
            <div className="mascota-aviso-ya-solicite">
              ✓ Ya enviaste tu solicitud para {mascota.nombre}. Te avisaremos cuando el equipo la revise.
            </div>
          )}

          <div className="mascota-modal-datos-grid">
            <div><span className="mascota-dato-label">Edad</span><span>{mascota.edad} {mascota.edad === 1 ? "año" : "años"}</span></div>
            <div><span className="mascota-dato-label">Raza</span><span>{mascota.raza || "—"}</span></div>
            <div><span className="mascota-dato-label">Peso</span><span>{mascota.peso ? `${mascota.peso} kg` : "—"}</span></div>
            <div><span className="mascota-dato-label">Estatura</span><span>{mascota.estatura || "—"}</span></div>
            <div><span className="mascota-dato-label">Vacunado</span><span>{mascota.vacunado ? "Sí 💉" : "No"}</span></div>
            <div><span className="mascota-dato-label">Esterilizado</span><span>{mascota.esterilizado ? "Sí ✂️" : "No"}</span></div>
          </div>

          {mascota.descripcion && (
            <>
              <h4>Sobre {mascota.nombre}</h4>
              <p>{mascota.descripcion}</p>
            </>
          )}

          <button
            className="mascota-btn-adoptar"
            disabled={!puedeAdoptar || cargandoEstado}
            onClick={onAdoptar}
          >
            {!disponible
              ? "No disponible por ahora"
              : yaSolicite
              ? "Ya enviaste tu solicitud ✓"
              : "Quiero adoptarlo 🏠"}
          </button>
        </div>
      </div>
    </div>
  );
}

export default Adopcion;