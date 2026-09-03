import api from "./api";

export const listarNotificaciones = () => api.get("/notificaciones");
export const contarNoLeidas = () => api.get("/notificaciones/no-leidas/contador");
export const marcarLeida = (id) => api.patch(`/notificaciones/${id}/leida`);
export const marcarTodasLeidas = () => api.patch("/notificaciones/todas/leidas");
export const eliminarNotificacion = (id) => api.delete(`/notificaciones/${id}`);
export const eliminarTodasNotificaciones = () => api.delete("/notificaciones/todas");