import { useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";
import { listarMascotas } from "../services/mascotaService";
import { imagenUrl } from "../services/api";
import "../styles/MascotasDestacadas.css";

export default function MascotasDestacadas() {
  const [mascotas, setMascotas] = useState([]);
  const [cargando, setCargando] = useState(true);
  const scrollRef = useRef(null);

  useEffect(() => {
    listarMascotas()
      .then((res) => {
        const disponibles = res.data
          .filter((m) => m.estado === "DISPONIBLE")
          .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
          .slice(0, 10);
        setMascotas(disponibles);
      })
      .finally(() => setCargando(false));
  }, []);

  const desplazar = (direccion) => {
    if (!scrollRef.current) return;
    scrollRef.current.scrollBy({ left: direccion * 260, behavior: "smooth" });
  };

  if (cargando) return null;
  if (mascotas.length === 0) return null;

  return (
    <section className="md-seccion">
      <div className="container">
        <div className="md-cabecera">
          <div>
            <span className="md-eyebrow">🐾 Recién llegados</span>
            <h2>Ellos te esperan</h2>
          </div>
          <div className="md-controles">
            <button onClick={() => desplazar(-1)} aria-label="Anterior">‹</button>
            <button onClick={() => desplazar(1)} aria-label="Siguiente">›</button>
            <Link to="/adopcion" className="md-ver-todos">Ver catálogo →</Link>
          </div>
        </div>
      </div>

      <div className="md-carrusel" ref={scrollRef}>
        {mascotas.map((m) => (
          <Link to="/adopcion" key={m.id} className="md-card">
            <div className="md-card-imagen">
              {m.imagen ? <img src={imagenUrl(m.imagen)} alt={m.nombre} /> : <div className="md-card-sin-imagen">🐾</div>}
            </div>
            <div className="md-card-info">
              <h3>{m.nombre}</h3>
              <p>{m.tipo === "perro" ? "🐶" : "🐱"} {m.edad} {m.edad === 1 ? "año" : "años"}</p>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}