import api from "./api";

export const listarSolicitudesAdmin = (estado) =>
  api.get("/admin/solicitudes-adopcion", { params: estado ? { estado } : {} });

export const aprobarSolicitud = (id) =>
  api.post(`/admin/solicitudes-adopcion/${id}/aprobar`);

export const rechazarSolicitud = (id, motivo) =>
  api.post(`/admin/solicitudes-adopcion/${id}/rechazar`, { motivo });