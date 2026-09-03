import api from "./api";

export const inscribirseEvento = (datos) => api.post("/inscripciones", datos);
export const misInscripciones = () => api.get("/inscripciones/mias");