import api from "./api";

export const listarEventosAdmin = (buscar) =>
  api.get("/admin/eventos", { params: buscar ? { buscar } : {} });

export const crearEvento = (datos) => api.post("/admin/eventos", datos);
export const actualizarEvento = (id, datos) => api.put(`/admin/eventos/${id}`, datos);

export const cambiarEstadoEvento = (id, estado) =>
  api.patch(`/admin/eventos/${id}/estado`, JSON.stringify(estado), {
    headers: { "Content-Type": "application/json" },
  });

export const eliminarEvento = (id) => api.delete(`/admin/eventos/${id}`);