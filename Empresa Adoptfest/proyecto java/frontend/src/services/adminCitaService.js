import api from "./api";

export const citasPendientes = () => api.get("/admin/citas/pendientes");
export const citasAgendadas = () => api.get("/admin/citas/agendadas");
export const agendarCita = (id, datos) => api.patch(`/admin/citas/${id}/agendar`, datos);