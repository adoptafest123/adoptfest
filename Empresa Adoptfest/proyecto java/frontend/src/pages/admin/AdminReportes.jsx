import { useEffect, useState } from "react";
import {
  PieChart, Pie, Cell, ResponsiveContainer, Tooltip,
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
} from "recharts";
import { obtenerReporteGeneral } from "../../services/adminReporteService";
import { useToast } from "../../context/ToastContext";
import "../../styles/AdminLayout.css";
import "../../styles/AdminReportes.css";

const TABS = [
  { valor: "resumen", label: "Resumen", icono: "📊" },
  { valor: "mascotas", label: "Mascotas", icono: "🐾" },
  { valor: "adopciones", label: "Adopciones", icono: "📋" },
  { valor: "eventos", label: "Eventos", icono: "🎉" },
  { valor: "donaciones", label: "Donaciones", icono: "💜" },
];

const COLORES_ESTADO = {
  DISPONIBLE: "#2d7a56",
  PROCESO: "#3a76ab",
  ADOPTADO: "#94a3b8",
  EN_EVENTO: "#e8a33d",
};

const ESTADO_LABEL = {
  DISPONIBLE: "Disponible",
  PROCESO: "En proceso",
  ADOPTADO: "Adoptado",
  EN_EVENTO: "En evento",
};

export default function AdminReportes() {
  const toast = useToast();
  const [tab, setTab] = useState("resumen");
  const [datos, setDatos] = useState(null);
  const [cargando, setCargando] = useState(true);

  useEffect(() => {
    obtenerReporteGeneral()
      .then((res) => setDatos(res.data))
      .catch(() => toast.error("No se pudo cargar el reporte."))
      .finally(() => setCargando(false));
  }, []);

  if (cargando) {
    return (
      <div>
        <div className="admin-cabecera"><div><h1>Reportes</h1></div></div>
        <div className="admin-vacio">Cargando…</div>
      </div>
    );
  }

  if (!datos) {
    return (
      <div>
        <div className="admin-cabecera"><div><h1>Reportes</h1></div></div>
        <div className="admin-vacio">No se pudo cargar la información.</div>
      </div>
    );
  }

  const datosGraficaMascotas = Object.entries(datos.mascotasPorEstado || {}).map(([estado, cantidad]) => ({
    estado,
    nombre: ESTADO_LABEL[estado] || estado,
    cantidad,
    color: COLORES_ESTADO[estado] || "#999",
  }));

  return (
    <div>
      <div className="admin-cabecera">
        <div>
          <h1>Reportes</h1>
          <p>Estadísticas generales de la plataforma</p>
        </div>
        <div className="as-tabs">
          {TABS.map((t) => (
            <button key={t.valor} className={`as-tab ${tab === t.valor ? "activo" : ""}`} onClick={() => setTab(t.valor)}>
              {t.icono} {t.label}
            </button>
          ))}
        </div>
      </div>

      {tab === "resumen" && (
        <div className="rp-grid">
          <TarjetaStat icono="🐾" color="#2d7a56" bg="#e4f3ea" numero={datos.totalMascotas ?? 0} label="Mascotas registradas" />
          <TarjetaStat icono="🏠" color="#3a76ab" bg="#e1eef6" numero={datos.totalAdopcionesCompletadas ?? 0} label="Adopciones completadas" />
          <TarjetaStat icono="⏳" color="#a3690c" bg="#fdf1de" numero={datos.solicitudesPendientes ?? 0} label="Solicitudes pendientes" />
          <TarjetaStat icono="🎉" color="#b8860b" bg="#fef3c7" numero={datos.totalEventos ?? 0} label="Eventos creados" />
          <TarjetaStat icono="🎟️" color="#3a76ab" bg="#e1eef6" numero={datos.totalInscripcionesEventos ?? 0} label="Inscripciones a eventos" />
          <TarjetaStat icono="💰" color="#1c6b45" bg="#e4f3ea" numero={`$${Number(datos.totalRecaudadoDinero ?? 0).toLocaleString("es-CO")}`} label="Recaudado en donaciones" />
          <TarjetaStat icono="📦" color="#a3690c" bg="#fdf1de" numero={datos.totalDonacionesEspecieConfirmadas ?? 0} label="Donaciones en especie" />
          <TarjetaStat icono="👥" color="#6f42c1" bg="#f0e9fb" numero={datos.totalUsuarios ?? 0} label="Usuarios registrados" />
        </div>
      )}

      {tab === "mascotas" && (
        <div className="rp-panel">
          <div className="rp-panel-grafica">
            <h3>Distribución por estado</h3>
            <ResponsiveContainer width="100%" height={280}>
              <PieChart>
                <Pie data={datosGraficaMascotas} dataKey="cantidad" nameKey="nombre" cx="50%" cy="50%" outerRadius={95} label>
                  {datosGraficaMascotas.map((d, i) => (
                    <Cell key={i} fill={d.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="rp-panel-leyenda">
            {datosGraficaMascotas.map((d) => (
              <div key={d.estado} className="rp-leyenda-item">
                <span className="rp-leyenda-punto" style={{ background: d.color }} />
                {d.nombre}: <strong>{d.cantidad}</strong>
              </div>
            ))}
            <div className="rp-total-mascotas">
              Total: <strong>{datos.totalMascotas}</strong> mascotas
            </div>
          </div>
        </div>
      )}

      {tab === "adopciones" && (
        <div className="rp-grid">
          <TarjetaStat icono="🏠" color="#2d7a56" bg="#e4f3ea" numero={datos.totalAdopcionesCompletadas ?? 0} label="Adopciones completadas" grande />
          <TarjetaStat icono="⏳" color="#a3690c" bg="#fdf1de" numero={datos.solicitudesPendientes ?? 0} label="Solicitudes pendientes de revisar" grande />
        </div>
      )}

      {tab === "eventos" && (
        <div className="rp-grid">
          <TarjetaStat icono="🎉" color="#b8860b" bg="#fef3c7" numero={datos.totalEventos ?? 0} label="Eventos creados en total" grande />
          <TarjetaStat icono="🎟️" color="#3a76ab" bg="#e1eef6" numero={datos.totalInscripcionesEventos ?? 0} label="Inscripciones registradas" grande />
        </div>
      )}

      {tab === "donaciones" && (
        <div className="rp-grid">
          <TarjetaStat icono="💰" color="#1c6b45" bg="#e4f3ea" numero={`$${Number(datos.totalRecaudadoDinero ?? 0).toLocaleString("es-CO")}`} label="Total recaudado en dinero" grande />
          <TarjetaStat icono="📦" color="#a3690c" bg="#fdf1de" numero={datos.totalDonacionesEspecieConfirmadas ?? 0} label="Donaciones en especie confirmadas" grande />
        </div>
      )}
    </div>
  );
}

function TarjetaStat({ icono, color, bg, numero, label, grande }) {
  return (
    <div className={`rp-tarjeta ${grande ? "rp-tarjeta-grande" : ""}`}>
      <span className="rp-tarjeta-icono" style={{ background: bg, color }}>{icono}</span>
      <div>
        <span className="rp-tarjeta-numero">{numero}</span>
        <span className="rp-tarjeta-label">{label}</span>
      </div>
    </div>
  );
}