// src/services/donacionService.js
import api from "./api";

// Esta función ya existía
export const misDonaciones = () => api.get("/donaciones/mias");

// --- NUEVAS FUNCIONES PARA DONACIONES ---

/**
 * Registra una nueva donación en especie.
 * @param {Object} datos - Los datos de la donación (categoria, especieDestino, descripcion, cantidad, direccionRecoleccion, telefonoContacto).
 * @returns {Promise} - Promesa de Axios.
 */
export const registrarDonacionEspecie = (datos) => {
  return api.post("/donaciones/especie", datos);
};

/**
 * Crea una orden de pago en PayPal para una donación en dinero.
 * @param {Object} datos - El monto a donar ({ monto: number }).
 * @returns {Promise} - Promesa de Axios que devuelve el link de aprobación de PayPal.
 */
export const crearOrdenDonacionDinero = (datos) => {
  return api.post("/donaciones/dinero/crear-orden", datos);
};

// --- FUNCIONES PARA EL ADMINISTRADOR ---

/**
 * Obtiene la lista de todas las donaciones para el panel de administración.
 * @returns {Promise} - Promesa de Axios.
 */
export const listarDonacionesAdmin = () => {
  return api.get("/admin/donaciones");
};

/**
 * Aprueba una donación en especie pendiente.
 * @param {number} id - El ID de la donación en especie.
 * @returns {Promise} - Promesa de Axios.
 */
export const aprobarDonacionEspecie = (id) => {
  return api.post(`/admin/donaciones/especie/${id}/aceptar`);
};

/**
 * Rechaza una donación en especie pendiente.
 * @param {number} id - El ID de la donación en especie.
 * @returns {Promise} - Promesa de Axios.
 */
export const rechazarDonacionEspecie = (id) => {
  return api.post(`/admin/donaciones/especie/${id}/rechazar`);
};

/**
 * Confirma que una donación en especie fue recibida.
 * @param {number} id - El ID de la donación en especie.
 * @returns {Promise} - Promesa de Axios.
 */
export const confirmarDonacionEspecie = (id) => {
  return api.post(`/admin/donaciones/especie/${id}/confirmar`);
};

/**
 * Elimina una donación en especie.
 * @param {number} id - El ID de la donación en especie.
 * @returns {Promise} - Promesa de Axios.
 */
export const eliminarDonacionEspecie = (id) => {
  return api.delete(`/admin/donaciones/especie/${id}`);
};

// --- FUNCIONES PARA PAYPAL (CONFIRMACIÓN/CANCELACIÓN) ---

/**
 * Confirma un pago de donación en dinero después del éxito en PayPal.
 * @param {string} orderId - El ID de la orden de PayPal (el token).
 * @returns {Promise} - Promesa de Axios.
 */
export const confirmarDonacionDinero = (orderId) => {
  return api.post("/donaciones/dinero/confirmar", { orderId });
};

/**
 * Cancela una orden de donación en dinero.
 * @param {string} orderId - El ID de la orden de PayPal.
 * @returns {Promise} - Promesa de Axios.
 */
export const cancelarDonacionDinero = (orderId) => {
  return api.post("/donaciones/dinero/cancelar", { orderId });
};