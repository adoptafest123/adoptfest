import api from "./api";

export const listarUsuarios = (buscar) => api.get("/admin/users", { params: { buscar } });
export const crearUsuario = (datos) => api.post("/admin/users", datos);
export const actualizarUsuario = (id, datos) => api.put(`/admin/users/${id}`, datos);
export const eliminarUsuario = (id) => api.delete(`/admin/users/${id}`);