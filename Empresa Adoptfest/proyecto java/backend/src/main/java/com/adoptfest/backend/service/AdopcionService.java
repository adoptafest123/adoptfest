package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.SolicitudAdopcionRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.*;
import lombok.RequiredArgsConstructor;

import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

import java.security.SecureRandom;
import java.time.LocalDate;
import java.time.LocalTime;
import java.util.List;

@Service
@RequiredArgsConstructor
public class AdopcionService {

    private final SolicitudAdopcionRepository solicitudRepository;
    private final MascotaRepository mascotaRepository;
    private final CitaRepository citaRepository;
    private final NotificacionService notificacionService; // la creamos más adelante

    @Transactional
    public SolicitudAdopcion crearSolicitud(User usuario, SolicitudAdopcionRequest datos) {
        Mascota mascota = mascotaRepository.findById(datos.mascotaId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Mascota no encontrada."));

        if (mascota.getEstado() != EstadoMascota.DISPONIBLE) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta mascota ya no está disponible para adopción.");
        }

        boolean yaTieneSolicitud = solicitudRepository
                .existsByUserIdAndMascotaIdAndEstado(usuario.getId(), mascota.getId(), EstadoSolicitud.PENDIENTE);
        if (yaTieneSolicitud) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Ya tienes una solicitud pendiente para esta mascota.");
        }

        SolicitudAdopcion solicitud = SolicitudAdopcion.builder()
                .user(usuario)
                .mascota(mascota)
                .nombreCompleto(datos.nombreCompleto())
                .cedula(datos.cedula())
                .telefono(datos.telefono())
                .direccion(datos.direccion())
                .ciudad(datos.ciudad())
                .tipoVivienda(datos.tipoVivienda())
                .tienePatio(datos.tienePatio())
                .esPropia(datos.esPropia())
                .tieneNinos(datos.tieneNinos())
                .edadesNinos(datos.edadesNinos())
                .tieneOtrosAnimales(datos.tieneOtrosAnimales())
                .cualesAnimales(datos.cualesAnimales())
                .personasEnCasa(datos.personasEnCasa())
                .tieneExperiencia(datos.tieneExperiencia())
                .descripcionExperiencia(datos.descripcionExperiencia())
                .horasSolaMascota(datos.horasSolaMascota())
                .quienCuidaAusencia(datos.quienCuidaAusencia())
                .motivoAdopcion(datos.motivoAdopcion())
                .compromiso(datos.compromiso())
                .estado(EstadoSolicitud.PENDIENTE)
                .build();

        SolicitudAdopcion guardada = solicitudRepository.save(solicitud);

        notificacionService.crear(usuario, "Tu solicitud de adopción para " + mascota.getNombre() + " fue enviada. Te avisaremos cuando sea revisada.");

        return guardada;
    }

        @Transactional
        public Cita aprobarYGenerarCita(Long solicitudId, LocalDate fecha, LocalTime hora, String lugar, String direccionCita) {
    SolicitudAdopcion solicitud = solicitudRepository.findById(solicitudId)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Solicitud no encontrada."));

    if (solicitud.getEstado() != EstadoSolicitud.PENDIENTE) {
        throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta solicitud ya fue procesada anteriormente.");
    }

    if (citaRepository.findBySolicitudId(solicitudId).isPresent()) {
        throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta solicitud ya tiene una cita creada.");
    }

    solicitud.setEstado(EstadoSolicitud.APROBADA);
    solicitudRepository.save(solicitud);

    Mascota mascota = solicitud.getMascota();
    mascota.setEstado(EstadoMascota.PROCESO);
    mascotaRepository.save(mascota);

    Cita cita = Cita.builder()
            .solicitud(solicitud)
            .user(solicitud.getUser())
            .mascota(mascota)
            .estado(EstadoCita.PENDIENTE_AGENDAR)
            .codigoVerificacion(generarCodigoVerificacion())
            .verificada(false)
            .build();

    Cita citaGuardada = citaRepository.save(cita);

    notificacionService.crear(
            solicitud.getUser(),
            "¡Tu solicitud para " + mascota.getNombre() + " fue aprobada! 🎉 Muy pronto te agendaremos una cita virtual para continuar el proceso — te avisaremos en cuanto quede lista."
    );

    // Las demás solicitudes pendientes de esta misma mascota quedan
    // automáticamente fuera de proceso, y se avisa a cada persona.
    List<SolicitudAdopcion> otras = solicitudRepository.findByMascotaIdAndEstado(mascota.getId(), EstadoSolicitud.PENDIENTE);
    for (SolicitudAdopcion otra : otras) {
        if (otra.getId().equals(solicitud.getId())) continue;

        otra.setEstado(EstadoSolicitud.RECHAZADA);
        otra.setObservaciones("La mascota fue asignada a otro adoptante.");
        solicitudRepository.save(otra);

        notificacionService.crear(
                otra.getUser(),
                mascota.getNombre() + " ya no está disponible en este momento — fue asignada a otro proceso de adopción. ¡Gracias por tu interés! Sigue explorando otras mascotas que te esperan 🐾."
        );
    }

    return citaGuardada;
}    

    public SolicitudAdopcion rechazar(Long solicitudId, String motivo) {
        SolicitudAdopcion solicitud = solicitudRepository.findById(solicitudId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Solicitud no encontrada."));

        if (solicitud.getEstado() != EstadoSolicitud.PENDIENTE) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta solicitud ya fue procesada anteriormente.");
        }

        solicitud.setEstado(EstadoSolicitud.RECHAZADA);
        solicitud.setObservaciones(motivo);
        solicitudRepository.save(solicitud);

        Mascota mascota = solicitud.getMascota();
                if (mascota.getEstado() == EstadoMascota.PROCESO) {
                        mascota.setEstado(EstadoMascota.DISPONIBLE);
                        mascotaRepository.save(mascota);
                }

        notificacionService.crear(
                solicitud.getUser(),
                "Tu solicitud para " + mascota.getNombre() + " no fue aprobada. Motivo: " + motivo
        );

        return solicitud;
    }

    /** Marca la cita como completada cuando el adoptante llega y muestra el código. */
    public Cita verificarCita(String codigoVerificacion) {
        Cita cita = citaRepository.findByCodigoVerificacion(codigoVerificacion)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Código de verificación inválido."));

        cita.setVerificada(true);
        cita.setEstado(EstadoCita.COMPLETADA);
        Cita citaGuardada = citaRepository.save(cita);

        Mascota mascota = cita.getMascota();
        mascota.setEstado(EstadoMascota.ADOPTADO);
        mascotaRepository.save(mascota);

        return citaGuardada;
    }

    private String generarCodigoVerificacion() {
        SecureRandom random = new SecureRandom();
        return String.valueOf(100000 + random.nextInt(900000)); // código de 6 dígitos
    }
}