import { Link } from "react-router-dom";
import "../styles/footer.css";

function Footer() {
  const anioActual = new Date().getFullYear();

  return (
    <footer className="footer">
      <div className="footer__cta">
        <div className="footer__cta-texto">
          <span className="footer__eyebrow">🐾 Adoptafest</span>
          <h3>¿Listo para cambiar una vida?</h3>
        </div>
        <div className="footer__cta-botones">
          <Link to="/adopcion" className="footer__btn footer__btn-primario">
            Adoptar ahora
          </Link>
          <Link to="/donaciones" className="footer__btn footer__btn-secundario">
            Quiero donar
          </Link>
        </div>
      </div>

      <div className="footer__inner">
        <div className="footer__col footer__marca">
          <h4>Adoptafest</h4>
          <p>
            Conectamos animales rescatados con familias que quieren darles
            un hogar. Cada adopción, donación o voluntariado suma.
          </p>
          <div className="footer__redes">
            <a href="#" aria-label="Facebook" className="footer__red">f</a>
            <a href="#" aria-label="Instagram" className="footer__red">◎</a>
            <a href="#" aria-label="TikTok" className="footer__red">♪</a>
          </div>
        </div>

        <div className="footer__col">
          <h5>Explorar</h5>
          <ul>
            <li><Link to="/">Inicio</Link></li>
            <li><Link to="/adopcion">Adoptar</Link></li>
            <li><Link to="/eventos">Eventos</Link></li>
            <li><Link to="/donaciones">Donar</Link></li>
          </ul>
        </div>

        <div className="footer__col">
          <h5>Ayuda</h5>
          <ul>
            <li><Link to="/registro">Crear cuenta</Link></li>
            <li><Link to="/login">Iniciar sesión</Link></li>
            <li><a href="#preguntas">Preguntas frecuentes</a></li>
          </ul>
        </div>

        <div className="footer__col">
          <h5>Contacto</h5>
          <p className="footer__contacto-item">📍 Bogotá, Colombia</p>
          <p className="footer__contacto-item">📞 +57 363 290 0392</p>
          <p className="footer__contacto-item">✉️ hola@adoptafest.org</p>
        </div>
      </div>

      <div className="footer__linea" />

      <div className="footer__final">
        <p className="footer__copy">© {anioActual} Adoptafest — Hecho con 🐾 para quienes no tienen voz.</p>
        <div className="footer__legales">
          <a href="#privacidad">Privacidad</a>
          <a href="#terminos">Términos</a>
        </div>
      </div>
    </footer>
  );
}

export default Footer;