import axios from "axios";
export const BACKEND_URL = "http://localhost:8080";

export const imagenUrl = (url) => {
  if (!url || /^(https?:|blob:|data:)/.test(url)) return url;
  return `${BACKEND_URL}${url}`;
};

const api = axios.create({
  baseURL: "http://localhost:8080/api",
});

// Se ejecuta ANTES de cada petición: si hay un token guardado, lo agrega
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Si el backend responde 401 (token vencido/inválido), cerramos sesión sola
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      localStorage.removeItem("usuario");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  }
  
);

export default api;