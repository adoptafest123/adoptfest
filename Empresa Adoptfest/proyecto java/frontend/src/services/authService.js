// src/services/authService.js
import api from "./api";

export const login = (correo, contrasena) =>
  api.post("/auth/login", { identificador: correo, contrasena });

export const registro = (nombre, correo, cedula, telefono, contrasena) =>
  api.post("/auth/registro", { nombre, correo, cedula, telefono, contrasena });