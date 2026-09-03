// src/pages/admin/AdminRefugiosDetalle.jsx
import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { obtenerEstadisticasRefugio } from "../../services/refugioService";
import { useToast } from "../../context/ToastContext";
import "../../styles/AdminLayout.css";

export default function AdminRefugiosDetalle() {
  const { id } = useParams();
  const navigate = useNavigate();
  const toast = useToast();
  const [cargando, setCargando] = useState(true);
  const [data, setData] = useState(null);

  useEffect(() => {
    obtenerEstadisticasRefugio(id)
      .then((res) => setData(res.data))
      .catch(() => toast.error("No se pudieron cargar los datos del refugio."))
      .finally(() => setCargando(false));
  }, [id]);

  if (cargando) return <div className="admin-vacio">Cargando...</div>;
  if (!data) return <div className="admin-vacio">Refugio no encontrado</div>;

  const { refugio, totalDonacionesDinero, totalRecaudadoDinero, totalDonacionesEspecie, donacionesPorCategoria } = data;

  const formatearMoneda = (monto) => {
    if (!monto) return "$0";
    return "$" + Number(monto).toLocaleString("es-CO");
  };

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <button className="admin-btn-cancelar" onClick={() => navigate("/admin/refugios")}>
            ← Volver
          </button>
          <h1 style={{ marginTop: "10px" }}>🏠 {refugio.nombre}</h1>
          <p>{refugio.direccion}</p>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "16px", marginBottom: "24px" }}>
        <div className="rp-tarjeta">
          <span className="rp-tarjeta-icono" style={{ background: "#e4f3ea", color: "#1c6b45" }}>💰</span>
          <div>
            <span className="rp-tarjeta-numero">{totalDonacionesDinero || 0}</span>
            <span className="rp-tarjeta-label">Donaciones en dinero</span>
          </div>
        </div>
        <div className="rp-tarjeta">
          <span className="rp-tarjeta-icono" style={{ background: "#fef3c7", color: "#f59e0b" }}>💵</span>
          <div>
            <span className="rp-tarjeta-numero">{formatearMoneda(totalRecaudadoDinero)}</span>
            <span className="rp-tarjeta-label">Total recaudado</span>
          </div>
        </div>
        <div className="rp-tarjeta">
          <span className="rp-tarjeta-icono" style={{ background: "#e1eef6", color: "#3a76ab" }}>📦</span>
          <div>
            <span className="rp-tarjeta-numero">{totalDonacionesEspecie || 0}</span>
            <span className="rp-tarjeta-label">Donaciones en especie</span>
          </div>
        </div>
      </div>

      {donacionesPorCategoria && Object.keys(donacionesPorCategoria).length > 0 && (
        <div className="admin-card" style={{ marginBottom: "24px" }}>
          <h3 style={{ padding: "16px 20px 0", margin: 0, fontFamily: "var(--font-display)", fontSize: "16px" }}>
            📊 Donaciones por categoría
          </h3>
          <table className="admin-tabla">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Cantidad</th>
              </tr>
            </thead>
            <tbody>
              {Object.entries(donacionesPorCategoria).map(([categoria, cantidad]) => (
                <tr key={categoria}>
                  <td>{categoria}</td>
                  <td>{cantidad}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <div style={{ display: "flex", gap: "10px" }}>
        <button className="admin-btn-cancelar" onClick={() => navigate("/admin/refugios")}>
          Volver al listado
        </button>
      </div>
    </div>
  );
}