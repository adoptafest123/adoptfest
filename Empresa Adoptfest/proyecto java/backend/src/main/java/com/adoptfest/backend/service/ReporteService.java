package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.ReporteGeneralResponse;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class ReporteService {

    private final MascotaRepository mascotaRepository;
    private final SolicitudAdopcionRepository solicitudAdopcionRepository;
    private final EventoRepository eventoRepository;
    private final InscripcionEventoRepository inscripcionEventoRepository;
    private final DonacionDineroRepository donacionDineroRepository;
    private final DonacionEspecieRepository donacionEspecieRepository;
    private final UserRepository userRepository;

    public ReporteGeneralResponse generarReporteGeneral() {
        var mascotas = mascotaRepository.findAll();

        Map<String, Long> porEstado = Arrays.stream(EstadoMascota.values())
                .collect(Collectors.toMap(
                        Enum::name,
                        estado -> mascotas.stream().filter(m -> m.getEstado() == estado).count()
                ));

        long adopcionesCompletadas = mascotas.stream()
                .filter(m -> m.getEstado() == EstadoMascota.ADOPTADO)
                .count();

        long solicitudesPendientes = solicitudAdopcionRepository
                .findByEstado(EstadoSolicitud.PENDIENTE).size();

        BigDecimal totalDinero = donacionDineroRepository
                .findByEstadoOrderByCreatedAtDesc(EstadoDonacionDinero.COMPLETADO)
                .stream()
                .map(DonacionDinero::getMonto)
                .reduce(BigDecimal.ZERO, BigDecimal::add);

        long donacionesEspecieConfirmadas = donacionEspecieRepository.findAllByOrderByCreatedAtDesc()
                .stream()
                .filter(d -> d.getEstado() == EstadoDonacionEspecie.CONFIRMADO)
                .count();

        return new ReporteGeneralResponse(
                mascotas.size(),
                porEstado,
                adopcionesCompletadas,
                solicitudesPendientes,
                eventoRepository.count(),
                inscripcionEventoRepository.count(),
                totalDinero,
                donacionesEspecieConfirmadas,
                userRepository.count()
        );
    }
}