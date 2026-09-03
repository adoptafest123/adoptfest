import React, { useState, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import "../styles/hero.css";
import mascota1 from "../assets/imagenes/mascota_1.jpg";
import mascota3 from "../assets/imagenes/mascota_3.jpg";
import mascota5 from "../assets/imagenes/mascota_5.jpg";

export default function Hero({
  slides = [
    { imagen: mascota1, titulo: "Un hogar para cada huella", textoBoton: "Adoptar ahora", linkBoton: "/adopcion" },
    { imagen: mascota3, titulo: "Haz la diferencia hoy", textoBoton: "Ver mascotas", linkBoton: "/adopcion" },
    { imagen: mascota5, titulo: "Ellos te esperan", textoBoton: "Explorar eventos", linkBoton: "/eventos" },
  ],
  eyebrow = "🐾 Adoptfest",
  variante = "teal",
  franjaInferior = null,
}) {
  const [activeIndex, setActiveIndex] = useState(0);
  const carruselRef = useRef(null);

  useEffect(() => {
    const el = carruselRef.current;
    if (!el) return;

    const handleSlid = (e) => {
      setActiveIndex(e.to);
    };

    el.addEventListener("slid.bs.carousel", handleSlid);
    return () => el.removeEventListener("slid.bs.carousel", handleSlid);
  }, []);

  return (
    <div className={`hero-envoltorio ${franjaInferior ? "hero-con-franja" : ""}`}>
      <div
        id="carruselPrincipal"
        ref={carruselRef}
        className={`carousel slide hero-carrusel hero-carrusel--${variante}`}
        data-bs-ride="carousel"
      >
        <div className="carousel-inner">
          {slides.map((slide, index) => (
            <div key={index} className={`carousel-item ${index === 0 ? "active" : ""}`}>
              <img src={slide.imagen} className="d-block w-100 carrusel-imagen" alt={slide.titulo} />
              <div className="carrusel-overlay" />
              <div className="carrusel-contenedor-texto">
                <div className="carrusel-caja-texto">
                  <span className="carrusel-eyebrow">{eyebrow}</span>
                  <h2>{slide.titulo}</h2>
                  <Link to={slide.linkBoton} className="carrusel-btn">{slide.textoBoton}</Link>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Indicadores: controlados por React, no por Bootstrap */}
        <div className="carrusel-indicadores">
          {slides.map((_, index) => (
            <button
              key={index}
              type="button"
              data-bs-target="#carruselPrincipal"
              data-bs-slide-to={index}
              className={index === activeIndex ? "active" : ""}
              aria-label={`Ir a la diapositiva ${index + 1}`}
            />
          ))}
        </div>

        <button className="carrusel-flecha carrusel-flecha-prev" type="button" data-bs-target="#carruselPrincipal" data-bs-slide="prev">
          <span aria-hidden="true">‹</span>
        </button>
        <button className="carrusel-flecha carrusel-flecha-next" type="button" data-bs-target="#carruselPrincipal" data-bs-slide="next">
          <span aria-hidden="true">›</span>
        </button>
      </div>

      {franjaInferior && <div className="hero-franja">{franjaInferior}</div>}
    </div>
  );
}
