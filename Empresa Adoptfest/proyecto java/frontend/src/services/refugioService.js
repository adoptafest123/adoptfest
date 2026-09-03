// src/services/refugioService.js
import api from "./api";

// ── Público ──
export const listarRefugios = () => api.get("/refugios");
export const obtenerRefugio = (id) => api.get(`/refugios/${id}`);

// ── Admin ──
export const listarRefugiosTodos = () => api.get("/refugios/admin/todos");
export const obtenerEstadisticasRefugios = () => api.get("/refugios/admin/estadisticas");
export const obtenerEstadisticasRefugio = (id) => api.get(`/refugios/admin/${id}/estadisticas`);
export const crearRefugio = (datos) => api.post("/refugios/admin", datos);
export const actualizarRefugio = (id, datos) => api.put(`/refugios/admin/${id}`, datos);
export const eliminarRefugio = (id) => api.delete(`/refugios/admin/${id}`);