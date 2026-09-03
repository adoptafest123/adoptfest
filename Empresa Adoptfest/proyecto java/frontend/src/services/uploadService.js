import api from "./api";

export const subirImagen = (archivo) => {
  const formData = new FormData();
  formData.append("archivo", archivo);

  return api.post("/uploads/imagen", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
};