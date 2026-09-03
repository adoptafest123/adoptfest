package com.adoptfest.backend.service;

import com.adoptfest.backend.model.Cita;
import com.adoptfest.backend.model.EstadoCita;
import com.adoptfest.backend.repository.CitaRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.LocalTime;
import java.util.List;

@Service
@RequiredArgsConstructor
public class CitaService {

    private final CitaRepository citaRepository;
    private final NotificacionService notificacionService;

    // Horario de atención del refugio para citas virtuales.
    // Cambia estos valores si el horario real es distinto.
    private static final LocalTime HORA_APERTURA = LocalTime.of(9, 0);
    private static final LocalTime HORA_CIERRE = LocalTime.of(17, 0);

    public List<Cita> pendientesDeAgendar() {
        return citaRepository.findByEstado(EstadoCita.PENDIENTE_AGENDAR);
    }

    public List<Cita> agendadas() {
        return citaRepository.findByEstado(EstadoCita.PROGRAMADA);
    }

    public Cita agendar(Long citaId, LocalDate fecha, LocalTime hora, String notas, String enlaceVirtual) {
        Cita cita = citaRepository.findById(citaId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Cita no encontrada."));

        if (cita.getEstado() != EstadoCita.PENDIENTE_AGENDAR) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta cita ya fue agendada o procesada.");
        }

        validarHorarioAtencion(fecha, hora);

        cita.setFecha(fecha);
        cita.setHora(hora);
        cita.setNotas(notas);
        cita.setEnlaceVirtual(enlaceVirtual);
        cita.setEstado(EstadoCita.PROGRAMADA);

        Cita guardada = citaRepository.save(cita);

        String mensaje = "📅 Tu cita virtual para adoptar a " + cita.getMascota().getNombre()
                + " quedó agendada para el " + fecha + " a las " + hora + ". "
                + (enlaceVirtual != null && !enlaceVirtual.isBlank()
                    ? "Enlace de la videollamada: " + enlaceVirtual
                    : "Te compartiremos el enlace de la videollamada pronto.");

        notificacionService.crear(cita.getUser(), mensaje);

        return guardada;
    }

    private void validarHorarioAtencion(LocalDate fecha, LocalTime hora) {
        if (fecha.isBefore(LocalDate.now())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "La fecha no puede ser en el pasado.");
        }
        if (fecha.getDayOfWeek() == DayOfWeek.SUNDAY) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "No agendamos citas los domingos.");
        }
        if (hora.isBefore(HORA_APERTURA) || hora.isAfter(HORA_CIERRE)) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "El horario de atención es de 9:00 a. m. a 5:00 p. m.");
        }
    }
}