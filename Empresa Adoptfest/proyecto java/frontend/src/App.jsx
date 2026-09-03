// src/App.jsx
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import { ToastProvider } from "./context/ToastContext";
import PerfilModal from "./components/PerfilModal";
import Inicio from "./pages/Inicio";
import AuthSliding from "./pages/AuthSliding";
import Adopcion from "./pages/Adopcion";
import Eventos from "./pages/Eventos";
import Donaciones from "./pages/Donaciones";
import DonacionPayPalConfirmacion from "./pages/DonacionPayPalConfirmacion";
import RutaProtegida from "./components/RutaProtegida";
import SolicitudAdopcion from "./pages/SolicitudAdopcion";
import AdminLayout from "./components/AdminLayout";
import AdminMascotas from "./pages/admin/AdminMascotas";
import AdminInscripciones from "./pages/admin/AdminInscripciones";
import AdminUsuarios from "./pages/admin/AdminUsuarios";
import AdminEventos from "./pages/admin/AdminEventos";
import AdminCitas from "./pages/admin/AdminCitas";
import AdminSolicitudes from "./pages/admin/AdminSolicitudes";
import AdminReportes from "./pages/admin/AdminReportes";
import AdminDonaciones from "./pages/admin/AdminDonaciones";

function App() {
  return (
    <AuthProvider>
      <ToastProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<Inicio />} />
            <Route path="/login" element={<AuthSliding />} />
            <Route path="/registro" element={<AuthSliding />} />
            <Route path="/adopcion" element={<Adopcion />} />
            <Route path="/eventos" element={<Eventos />} />
            <Route path="/donaciones" element={<Donaciones />} />
            
            <Route path="/donaciones/paypal/exito" element={<DonacionPayPalConfirmacion />} />
            <Route path="/donaciones/paypal/cancelado" element={<DonacionPayPalConfirmacion />} />
            
            <Route
              path="/adopcion/:id/solicitud"
              element={
                <RutaProtegida>
                  <SolicitudAdopcion />
                </RutaProtegida>
              }
            />
            
            <Route
              path="/admin"
              element={
                <RutaProtegida soloAdmin>
                  <AdminLayout />
                </RutaProtegida>
              }
            >
              <Route index element={<Navigate to="usuarios" replace />} />
              <Route path="mascotas" element={<AdminMascotas />} />
              <Route path="usuarios" element={<AdminUsuarios />} />
              <Route path="reportes" element={<AdminReportes />} />
              <Route path="informes" element={<AdminReportes />} />
              <Route path="inscripciones" element={<AdminInscripciones />} />
              <Route path="solicitudes" element={<AdminSolicitudes />} />
              <Route path="eventos" element={<AdminEventos />} />
              <Route path="citas" element={<AdminCitas />} />
              <Route path="donaciones" element={<AdminDonaciones />} />
            </Route>
          </Routes>
          <PerfilModal />
        </BrowserRouter>
      </ToastProvider>
    </AuthProvider>
  );
}

export default App;