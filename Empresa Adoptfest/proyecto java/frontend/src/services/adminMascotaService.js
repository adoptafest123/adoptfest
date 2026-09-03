import api from "./api";

export const listarMascotasAdmin = () => api.get("/admin/mascotas");
export const crearMascota = (datos) => api.post("/admin/mascotas", datos);
export const actualizarMascota = (id, datos) => api.put(`/admin/mascotas/${id}`, datos);
export const eliminarMascota = (id) => api.delete(`/admin/mascotas/${id}`);