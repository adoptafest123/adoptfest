import api from "./api";

export const obtenerPerfil = () => api.get("/users/me");

export const actualizarPerfil = ({ nombre, telefono, foto, descripcion }) =>
  api.put("/users/me/perfil", { nombre, telefono, foto, descripcion });