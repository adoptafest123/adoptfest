// src/main/java/com/adoptfest/backend/service/DonacionService.java
package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.CrearDonacionDineroRequest;
import com.adoptfest.backend.dto.DonacionEspecieRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.DonacionDineroRepository;
import com.adoptfest.backend.repository.DonacionEspecieRepository;
import com.adoptfest.backend.repository.RefugioRepository;
import com.adoptfest.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

import java.math.RoundingMode;
import java.util.Map;

@Slf4j
@Service
@RequiredArgsConstructor
public class DonacionService {

    private final DonacionDineroRepository donacionDineroRepository;
    private final DonacionEspecieRepository donacionEspecieRepository;
    private final UserRepository userRepository;
    private final RefugioRepository refugioRepository;
    private final PayPalService payPalService;
    private final NotificacionService notificacionService;

    // ═══════════════════════════════════════════════════════════
    // DINERO
    // ═══════════════════════════════════════════════════════════

    @Transactional
    public Map<String, Object> crearOrdenDinero(User usuario, CrearDonacionDineroRequest datos, String moneda) {
        try {
            log.info("💰 Creando orden de donación para usuario: {}", usuario.getEmail());
            log.info("📊 Monto: {}, Moneda: {}, Refugio ID: {}", datos.monto(), moneda, datos.refugioId());

            Map<String, Object> orden = payPalService.crearOrden(datos.monto(), moneda);

            if (orden == null || orden.get("id") == null) {
                log.error("❌ PayPal no devolvió un ID de orden. Respuesta: {}", orden);
                throw new ResponseStatusException(
                        HttpStatus.BAD_GATEWAY,
                        "No se pudo conectar con PayPal. Intenta de nuevo en un momento."
                );
            }

            String linkAprobacion = payPalService.extraerLinkAprobacion(orden);
            if (linkAprobacion == null) {
                log.error("❌ PayPal no devolvió un link de aprobación. Orden: {}", orden);
                throw new ResponseStatusException(
                        HttpStatus.BAD_GATEWAY,
                        "PayPal no devolvió un link de pago válido."
                );
            }

            String orderId = (String) orden.get("id");
            log.info("✅ Orden PayPal creada. Order ID: {}", orderId);

            Refugio refugio = null;
            if (datos.refugioId() != null) {
                refugio = refugioRepository.findById(datos.refugioId())
                        .orElseThrow(() -> new ResponseStatusException(
                                HttpStatus.NOT_FOUND,
                                "Refugio no encontrado"
                        ));
            }

            DonacionDinero donacion = DonacionDinero.builder()
                    .user(usuario)
                    .refugio(refugio)
                    .monto(datos.monto())
                    .moneda(moneda)
                    .paypalOrderId(orderId)
                    .estado(EstadoDonacionDinero.PENDIENTE)
                    .build();

            donacion = donacionDineroRepository.save(donacion);
            log.info("✅ Donación guardada en BD. ID: {}", donacion.getId());

            return Map.of(
                    "linkAprobacion", linkAprobacion,
                    "ordenId", orderId
            );

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error inesperado al crear orden de donación: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al procesar la donación: " + e.getMessage()
            );
        }
    }

    @Transactional
    public DonacionDinero confirmarPagoDinero(String orderId) {
        try {
            log.info("📦 Confirmando pago de PayPal. Order ID: {}", orderId);

            DonacionDinero donacion = donacionDineroRepository.findByPaypalOrderId(orderId)
                    .orElseThrow(() -> {
                        log.error("❌ Donación no encontrada para Order ID: {}", orderId);
                        return new ResponseStatusException(
                                HttpStatus.NOT_FOUND,
                                "No encontramos esa donación en nuestros registros."
                        );
                    });

            log.info("📋 Donación encontrada. Estado actual: {}", donacion.getEstado());

            if (donacion.getEstado() == EstadoDonacionDinero.COMPLETADO) {
                log.info("✅ Donación ya estaba completada. ID: {}", donacion.getId());
                return donacion;
            }

            Map<String, Object> resultado = payPalService.capturarOrden(orderId);
            log.info("📥 Respuesta de captura PayPal: {}", resultado);

            String status = (String) resultado.get("status");
            boolean pagoExitoso = resultado != null && "COMPLETED".equals(status);

            if (!pagoExitoso) {
                log.warn("⚠️ Pago no completado. Status: {}", status);
                donacion.setEstado(EstadoDonacionDinero.FALLIDO);
                donacionDineroRepository.save(donacion);
                throw new ResponseStatusException(
                        HttpStatus.PAYMENT_REQUIRED,
                        "El pago no pudo confirmarse. Intenta de nuevo."
                );
            }

            int puntos = donacion.getMonto().setScale(0, RoundingMode.HALF_UP).intValue();

            donacion.setEstado(EstadoDonacionDinero.COMPLETADO);
            donacion.setPuntosOtorgados(puntos);
            donacion = donacionDineroRepository.save(donacion);
            log.info("✅ Donación completada. Puntos otorgados: {}", puntos);

            User usuario = donacion.getUser();
            usuario.sumarPuntosDonante(puntos);
            userRepository.save(usuario);
            log.info("✅ Usuario actualizado. Nuevos puntos: {}", usuario.getPuntosDonante());

            String mensajeNotificacion = "Has donado $" + donacion.getMonto() + " " + donacion.getMoneda() +
                    " y has ganado " + puntos + " puntos de donante. ¡Tu apoyo ayuda a más mascotas a encontrar hogar!";
            
            if (donacion.getRefugio() != null) {
                mensajeNotificacion += " Beneficio al refugio: " + donacion.getRefugio().getNombre();
            }

            notificacionService.crear(
                    usuario,
                    "🎉 ¡Gracias por tu donación!",
                    mensajeNotificacion,
                    TipoNotificacion.EXITO
            );

            return donacion;

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error al confirmar pago de PayPal: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al confirmar el pago: " + e.getMessage()
            );
        }
    }

    @Transactional
    public void cancelarDinero(String orderId) {
        try {
            log.info("❌ Cancelando orden PayPal. Order ID: {}", orderId);

            donacionDineroRepository.findByPaypalOrderId(orderId).ifPresent(d -> {
                if (d.getEstado() == EstadoDonacionDinero.PENDIENTE) {
                    d.setEstado(EstadoDonacionDinero.FALLIDO);
                    donacionDineroRepository.save(d);
                    log.info("✅ Donación marcada como FALLIDA. ID: {}", d.getId());
                } else {
                    log.info("ℹ️ Donación no estaba PENDIENTE. Estado actual: {}", d.getEstado());
                }
            });

        } catch (Exception e) {
            log.error("❌ Error al cancelar orden: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al cancelar la donación: " + e.getMessage()
            );
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ESPECIE
    // ═══════════════════════════════════════════════════════════

    @Transactional
    public DonacionEspecie registrarEspecie(User usuario, DonacionEspecieRequest datos) {
        try {
            log.info("📦 Registrando donación en especie para usuario: {}", usuario.getEmail());
            log.info("📋 Categoría: {}, Cantidad: {}, Refugio ID: {}", 
                datos.categoria(), datos.cantidad(), datos.refugioId());

            Refugio refugio = null;
            if (datos.refugioId() != null) {
                refugio = refugioRepository.findById(datos.refugioId())
                        .orElseThrow(() -> new ResponseStatusException(
                                HttpStatus.NOT_FOUND,
                                "Refugio no encontrado"
                        ));
            }

            DonacionEspecie donacion = DonacionEspecie.builder()
                    .user(usuario)
                    .refugio(refugio)
                    .categoria(datos.categoria())
                    .especieDestino(datos.especieDestino())
                    .descripcion(datos.descripcion())
                    .cantidad(datos.cantidad())
                    .direccionRecoleccion(datos.direccionRecoleccion())
                    .telefonoContacto(datos.telefonoContacto())
                    .estado(EstadoDonacionEspecie.PENDIENTE)
                    .build();

            DonacionEspecie guardada = donacionEspecieRepository.save(donacion);
            log.info("✅ Donación en especie registrada. ID: {}", guardada.getId());

            String mensajeNotificacion = "Tu donación de " + datos.categoria() + 
                    " fue registrada y está pendiente de revisión. Te notificaremos cuándo pasaremos a recogerla.";
            
            if (refugio != null) {
                mensajeNotificacion += " Beneficiará al refugio: " + refugio.getNombre();
            }

            notificacionService.crear(
                    usuario,
                    "📦 Donación registrada",
                    mensajeNotificacion,
                    TipoNotificacion.INFO
            );

            return guardada;

        } catch (Exception e) {
            log.error("❌ Error al registrar donación en especie: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al registrar la donación: " + e.getMessage()
            );
        }
    }

    @Transactional
    public DonacionEspecie aceptarEspecie(Long id) {
        try {
            log.info("✅ Aceptando donación en especie. ID: {}", id);

            DonacionEspecie donacion = obtenerEspecieOFallar(id);

            if (donacion.getEstado() != EstadoDonacionEspecie.PENDIENTE) {
                log.warn("⚠️ Donación no está PENDIENTE. Estado actual: {}", donacion.getEstado());
                throw new ResponseStatusException(
                        HttpStatus.CONFLICT,
                        "Esta donación ya fue procesada anteriormente."
                );
            }

            donacion.setEstado(EstadoDonacionEspecie.APROBADO);
            DonacionEspecie guardada = donacionEspecieRepository.save(donacion);
            log.info("✅ Donación aprobada. ID: {}", guardada.getId());

            return guardada;

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error al aceptar donación: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al aceptar la donación: " + e.getMessage()
            );
        }
    }

    @Transactional
    public DonacionEspecie rechazarEspecie(Long id) {
        try {
            log.info("❌ Rechazando donación en especie. ID: {}", id);

            DonacionEspecie donacion = obtenerEspecieOFallar(id);

            if (donacion.getEstado() != EstadoDonacionEspecie.PENDIENTE) {
                log.warn("⚠️ Donación no está PENDIENTE. Estado actual: {}", donacion.getEstado());
                throw new ResponseStatusException(
                        HttpStatus.CONFLICT,
                        "Esta donación ya fue procesada anteriormente."
                );
            }

            donacion.setEstado(EstadoDonacionEspecie.RECHAZADO);
            DonacionEspecie guardada = donacionEspecieRepository.save(donacion);
            log.info("✅ Donación rechazada. ID: {}", guardada.getId());

            notificacionService.crear(
                    donacion.getUser(),
                    "❌ Donación no pudo procesarse",
                    "Lamentablemente no pudimos procesar tu donación de " +
                            donacion.getCategoria() + " en este momento. ¡Gracias por tu intención de ayudar!",
                    TipoNotificacion.RECHAZADO
            );

            return guardada;

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error al rechazar donación: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al rechazar la donación: " + e.getMessage()
            );
        }
    }

    @Transactional
    public DonacionEspecie confirmarEspecie(Long id) {
        try {
            log.info("📦 Confirmando recolección de donación en especie. ID: {}", id);

            DonacionEspecie donacion = obtenerEspecieOFallar(id);

            int puntos = donacion.calcularPuntos();
            log.info("📊 Puntos calculados: {}", puntos);

            donacion.setEstado(EstadoDonacionEspecie.CONFIRMADO);
            donacion.setPuntosOtorgados(puntos);
            donacion.setConfirmadoAt(java.time.LocalDateTime.now());

            DonacionEspecie guardada = donacionEspecieRepository.save(donacion);
            log.info("✅ Donación confirmada. ID: {}", guardada.getId());

            User usuario = donacion.getUser();
            usuario.sumarPuntosDonante(puntos);
            userRepository.save(usuario);
            log.info("✅ Usuario actualizado. Nuevos puntos: {}", usuario.getPuntosDonante());

            String mensajeNotificacion = "Tu donación de " + donacion.getCategoria() + 
                    " fue recibida exitosamente. Has ganado " + puntos + " puntos de donante. ¡Gracias por tu generosidad!";
            
            if (donacion.getRefugio() != null) {
                mensajeNotificacion += " Beneficiaste al refugio: " + donacion.getRefugio().getNombre();
            }

            notificacionService.crear(
                    usuario,
                    "🎉 ¡Recibimos tu donación!",
                    mensajeNotificacion,
                    TipoNotificacion.EXITO
            );

            return guardada;

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error al confirmar donación en especie: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al confirmar la donación: " + e.getMessage()
            );
        }
    }

    @Transactional
    public void eliminarEspecie(Long id) {
        try {
            log.info("🗑️ Eliminando donación en especie. ID: {}", id);

            DonacionEspecie donacion = obtenerEspecieOFallar(id);

            notificacionService.crear(
                    donacion.getUser(),
                    "🗑️ Donación eliminada",
                    "Tu donación de \"" + donacion.getCategoria().name() + "\" fue eliminada por el administrador.",
                    TipoNotificacion.INFO
            );

            donacionEspecieRepository.delete(donacion);
            log.info("✅ Donación eliminada. ID: {}", id);

        } catch (ResponseStatusException e) {
            throw e;
        } catch (Exception e) {
            log.error("❌ Error al eliminar donación: ", e);
            throw new ResponseStatusException(
                    HttpStatus.INTERNAL_SERVER_ERROR,
                    "Error al eliminar la donación: " + e.getMessage()
            );
        }
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTODOS AUXILIARES
    // ═══════════════════════════════════════════════════════════

    private DonacionEspecie obtenerEspecieOFallar(Long id) {
        return donacionEspecieRepository.findById(id)
                .orElseThrow(() -> {
                    log.error("❌ Donación en especie no encontrada. ID: {}", id);
                    return new ResponseStatusException(
                            HttpStatus.NOT_FOUND,
                            "Donación no encontrada."
                    );
                });
    }
}