import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { listarEventos } from "../services/eventoService";
import { imagenUrl } from "../services/api";
import "../styles/EventosDestacados.css";

const CATEGORIA_INFO = {
  ADOPCION: { texto: "Adopción", clase: "cat-adopcion" },
  EDUCACION: { texto: "Educación", clase: "cat-educacion" },
  RECREACION: { texto: "Recreación", clase: "cat-recreacion" },
};

function tiempoRestante(fechaISO) {
  const ahora = new Date();
  const fecha = new Date(fechaISO);
  const diffMs = fecha - ahora;

  if (diffMs <= 0) return "En curso";

  const dias = Math.floor(diffMs / (1000 * 60 * 60 * 24));
  const horas = Math.floor((diffMs / (1000 * 60 * 60)) % 24);

  if (dias === 0 && horas === 0) return "¡Es en minutos!";
  if (dias === 0) return `Faltan ${horas} ${horas === 1 ? "hora" : "horas"}`;
  if (dias === 1) return "Es mañana";
  return `Faltan ${dias} días`;
}

function formatearFecha(fechaISO) {
  return new Date(fechaISO).toLocaleDateString("es-CO", { day: "numeric", month: "short" });
}

export default function EventosDestacados() {
  const [eventos, setEventos] = useState([]);
  const [cargando, setCargando] = useState(true);

  useEffect(() => {
    listarEventos()
      .then((res) => {
        const proximos = res.data
          .filter((e) => e.estado === "ACTIVO" && new Date(e.fecha) >= new Date())
          .sort((a, b) => new Date(a.fecha) - new Date(b.fecha))
          .slice(0, 3);
        setEventos(proximos);
      })
      .finally(() => setCargando(false));
  }, []);

  if (cargando) return null;
  if (eventos.length === 0) return null;

  return (
    <section className="ed-seccion">
      <div className="container">ay
        <div className="ed-cabecera">
          <div>
            <span className="ed-eyebrow">🎉 No te los pierdas</span>
            <h2>Próximos eventos</h2>
          </div>
          <Link to="/eventos" className="ed-ver-todos">Ver todos →</Link>
        </div>

        <div className="ed-grid">
          {eventos.map((ev) => {
            const cat = CATEGORIA_INFO[ev.categoria] ?? CATEGORIA_INFO.ADOPCION;
            return (
              <Link to="/eventos" key={ev.id} className="ed-card">
                <div className="ed-card-imagen">
                  {ev.imagen ? (
                    <img src={imagenUrl(ev.imagen)} alt={ev.titulo} />
                  ) : (
                    <div className="ed-card-sin-imagen">🎉</div>
                  )}
                  <span className={`ed-cuenta-regresiva ${ev.estado === "ACTIVO" ? "" : "pasado"}`}>
                    {tiempoRestante(ev.fecha)}
                  </span>
                </div>
                <div className="ed-card-info">
                  <span className={`ed-categoria ${cat.clase}`}>{cat.texto}</span>
                  <h3>{ev.titulo}</h3>
                  <p>📅 {formatearFecha(ev.fecha)} {ev.lugar && `· 📍 ${ev.lugar}`}</p>
                </div>
              </Link>
            );
          })}
        </div>
      </div>
    </section>
  );
}