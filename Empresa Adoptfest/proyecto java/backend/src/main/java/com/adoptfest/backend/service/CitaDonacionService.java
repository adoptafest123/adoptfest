package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.CitaDonacionRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.CitaDonacionRepository;
import com.adoptfest.backend.repository.DonacionEspecieRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
@RequiredArgsConstructor
public class CitaDonacionService {

    private final CitaDonacionRepository citaDonacionRepository;
    private final DonacionEspecieRepository donacionEspecieRepository;
    private final DonacionService donacionService;

    /** El admin agenda la recolección solo después de haber aceptado la donación. */
    public CitaDonacion agendar(CitaDonacionRequest datos) {
        DonacionEspecie donacion = donacionEspecieRepository.findById(datos.donacionEspecieId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Donación no encontrada."));

        if (donacion.getEstado() != EstadoDonacionEspecie.APROBADO) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Solo se puede agendar recolección de donaciones ya aprobadas.");
        }

        if (citaDonacionRepository.findByDonacionEspecieId(donacion.getId()).isPresent()) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta donación ya tiene una cita agendada.");
        }

        CitaDonacion cita = CitaDonacion.builder()
                .user(donacion.getUser())
                .donacionEspecie(donacion)
                .fecha(datos.fecha())
                .hora(datos.hora())
                .direccionRecoleccion(donacion.getDireccionRecoleccion())
                .notas(datos.notas())
                .estado(EstadoCitaDonacion.PROGRAMADA)
                .build();

        return citaDonacionRepository.save(cita);
    }

    /** Cuando el equipo recoge de verdad la donación: marca completada Y otorga los puntos. */
    public CitaDonacion completar(Long citaId) {
        CitaDonacion cita = citaDonacionRepository.findById(citaId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Cita no encontrada."));

        cita.setEstado(EstadoCitaDonacion.COMPLETADA);
        CitaDonacion guardada = citaDonacionRepository.save(cita);

        donacionService.confirmarEspecie(cita.getDonacionEspecie().getId());

        return guardada;
    }

    public CitaDonacion cancelar(Long citaId) {
        CitaDonacion cita = citaDonacionRepository.findById(citaId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Cita no encontrada."));

        cita.setEstado(EstadoCitaDonacion.CANCELADA);
        return citaDonacionRepository.save(cita);
    }
}