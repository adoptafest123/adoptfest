// src/pages/DonacionPayPalConfirmacion.jsx
import { useEffect, useState } from "react";
import { useNavigate, useSearchParams, useLocation, Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useToast } from "../context/ToastContext";
import { 
  confirmarDonacionDinero, 
  cancelarDonacionDinero 
} from "../services/donacionService";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

function DonacionPayPalConfirmacion() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const location = useLocation();
  const toast = useToast();
  const { usuario } = useAuth();
  const [estado, setEstado] = useState("procesando");
  const [mensaje, setMensaje] = useState("");
  const [puntosGanados, setPuntosGanados] = useState(0);
  const [nivelActual, setNivelActual] = useState("");

  useEffect(() => {
    const token = searchParams.get("token");
    const esRutaExito = location.pathname.includes("exito");
    const esRutaCancelado = location.pathname.includes("cancelado");

    if (!token) {
      setEstado("error");
      setMensaje("No se encontró información de la transacción.");
      return;
    }

    if (esRutaExito) {
      confirmarDonacionDinero(token)
        .then((response) => {
          const donacion = response.data;
          const puntos = donacion.puntosOtorgados || 0;
          setPuntosGanados(puntos);
          
          // Calcular el nivel del donante
          const nivel = calcularNivel(usuario?.puntosDonante || 0 + puntos);
          setNivelActual(nivel);
          
          setEstado("exito");
          setMensaje("🎉 ¡Gracias por tu donación! Tu apoyo es invaluable para los animales.");
          toast.exito(`¡Donación completada! Has ganado ${puntos} puntos de donante.`);
          
          // Actualizar el usuario en el contexto
          if (usuario) {
            const usuarioActualizado = {
              ...usuario,
              puntosDonante: (usuario.puntosDonante || 0) + puntos
            };
            localStorage.setItem("usuario", JSON.stringify(usuarioActualizado));
          }
          
          // Redirigir después de 5 segundos
          setTimeout(() => navigate("/donaciones"), 5000);
        })
        .catch((error) => {
          setEstado("error");
          setMensaje(error.response?.data?.mensaje || "No se pudo confirmar la donación. Por favor, contacta a soporte.");
        });
    } else if (esRutaCancelado) {
      cancelarDonacionDinero(token)
        .then(() => {
          setEstado("cancelado");
          setMensaje("Cancelaste el proceso de donación. ¡Esperamos verte de nuevo!");
          toast.info("Donación cancelada.");
          setTimeout(() => navigate("/donaciones"), 3000);
        })
        .catch(() => {
          setEstado("error");
          setMensaje("Ocurrió un error al cancelar la transacción.");
        });
    } else {
      setEstado("error");
      setMensaje("Ruta inválida para confirmar la donación.");
    }
  }, [searchParams, navigate, location.pathname, toast, usuario]);

  const calcularNivel = (puntos) => {
    if (puntos >= 1000) return "Oro 🏅";
    if (puntos >= 500) return "Plata 🥈";
    if (puntos >= 200) return "Bronce 🥉";
    return "Apoyo 💚";
  };

  return (
    <>
      <Navbar />
      <div className="container" style={{ maxWidth: "600px", marginTop: "150px", marginBottom: "60px" }}>
        <div className="card shadow-lg p-5 text-center" style={{ borderRadius: "20px", border: "none" }}>
          
          {estado === "procesando" && (
            <>
              <div className="spinner-border text-success" role="status" style={{ width: "4rem", height: "4rem" }}>
                <span className="visually-hidden">Procesando...</span>
              </div>
              <h3 className="mt-4" style={{ fontFamily: "var(--font-display)" }}>Confirmando tu donación...</h3>
              <p className="text-muted">Por favor espera un momento mientras procesamos tu pago.</p>
            </>
          )}

          {estado === "exito" && (
            <>
              <div style={{ fontSize: "5rem" }}>🎉</div>
              <h3 className="mt-3 text-success" style={{ fontFamily: "var(--font-display)" }}>¡Donación Exitosa!</h3>
              <div className="mt-3 p-3 bg-light rounded-3" style={{ borderRadius: "15px" }}>
                <p className="mb-2" style={{ fontSize: "1.1rem" }}>{mensaje}</p>
                <div className="mt-3 d-flex justify-content-center gap-4 flex-wrap">
                  <div className="p-3 bg-success bg-opacity-10 rounded-3">
                    <span className="d-block" style={{ fontSize: "0.8rem", color: "#6c757d" }}>Puntos ganados</span>
                    <span className="fw-bold" style={{ fontSize: "1.5rem", color: "#198754" }}>+{puntosGanados}</span>
                  </div>
                  <div className="p-3 bg-warning bg-opacity-10 rounded-3">
                    <span className="d-block" style={{ fontSize: "0.8rem", color: "#6c757d" }}>Nivel de donante</span>
                    <span className="fw-bold" style={{ fontSize: "1.5rem", color: "#ffc107" }}>{nivelActual}</span>
                  </div>
                  <div className="p-3 bg-info bg-opacity-10 rounded-3">
                    <span className="d-block" style={{ fontSize: "0.8rem", color: "#6c757d" }}>Puntos totales</span>
                    <span className="fw-bold" style={{ fontSize: "1.5rem", color: "#0dcaf0" }}>
                      {(usuario?.puntosDonante || 0) + puntosGanados}
                    </span>
                  </div>
                </div>
              </div>
              <p className="text-muted small mt-3">Serás redirigido en unos segundos...</p>
              <div className="mt-3">
                <Link to="/donaciones" className="btn btn-success" style={{ borderRadius: "999px", padding: "10px 30px" }}>
                  Volver a Donaciones
                </Link>
              </div>
            </>
          )}

          {estado === "cancelado" && (
            <>
              <div style={{ fontSize: "4rem" }}>👋</div>
              <h3 className="mt-3 text-warning" style={{ fontFamily: "var(--font-display)" }}>Donación Cancelada</h3>
              <p className="mt-2">{mensaje}</p>
              <p className="text-muted small">Serás redirigido en unos segundos...</p>
              <div className="mt-3">
                <Link to="/donaciones" className="btn btn-warning" style={{ borderRadius: "999px", padding: "10px 30px" }}>
                  Volver a Donaciones
                </Link>
              </div>
            </>
          )}

          {estado === "error" && (
            <>
              <div style={{ fontSize: "4rem" }}>😓</div>
              <h3 className="mt-3 text-danger" style={{ fontFamily: "var(--font-display)" }}>Algo salió mal</h3>
              <p className="mt-2">{mensaje}</p>
              <div className="mt-3">
                <Link to="/donaciones" className="btn btn-primary" style={{ borderRadius: "999px", padding: "10px 30px" }}>
                  Volver a Donaciones
                </Link>
              </div>
            </>
          )}
        </div>
      </div>
      <Footer />
    </>
  );
}

export default DonacionPayPalConfirmacion;