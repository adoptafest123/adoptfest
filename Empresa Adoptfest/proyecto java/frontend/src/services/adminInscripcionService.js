import api from "./api";

export const listarInscripcionesAdmin = (eventoId) =>
  api.get("/admin/inscripciones", { params: eventoId ? { eventoId } : {} });

export const aceptarInscripcion = (id) => api.post(`/admin/inscripciones/${id}/aceptar`);
export const rechazarInscripcion = (id, motivo) =>
  api.post(`/admin/inscripciones/${id}/rechazar`, { motivo });