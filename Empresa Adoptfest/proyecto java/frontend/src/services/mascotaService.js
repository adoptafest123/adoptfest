import api from "./api";

export const listarMascotas = () => api.get("/mascotas");
export const obtenerMascota = (id) => api.get(`/mascotas/${id}`);