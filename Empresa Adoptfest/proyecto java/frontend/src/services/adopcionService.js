import api from "./api";

export const crearSolicitudAdopcion = (datos) => api.post("/solicitudes-adopcion", datos);
export const misSolicitudes = () => api.get("/solicitudes-adopcion/mias");
export const interesMascota = (id) => api.get(`/mascotas/${id}/interes`);
export const yaSolicitéMascota = (mascotaId) => api.get(`/solicitudes-adopcion/mascota/${mascotaId}/mia`);