import api from "./api";

export const obtenerReporteGeneral = () => api.get("/admin/reportes/general");