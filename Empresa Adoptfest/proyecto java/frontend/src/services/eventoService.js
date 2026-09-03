import api from "./api";

export const obtenerCuposEvento = (id) => api.get(`/eventos/${id}/cupos`);
export const listarEventos = () => api.get("/eventos");
export const obtenerEvento = (id) => api.get(`/eventos/${id}`);