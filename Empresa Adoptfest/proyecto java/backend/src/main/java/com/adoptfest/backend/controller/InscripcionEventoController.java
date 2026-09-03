package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.InscripcionEventoRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.EventoRepository;
import com.adoptfest.backend.repository.InscripcionEventoRepository;
import com.adoptfest.backend.service.NotificacionService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Map;

@RestController
@RequiredArgsConstructor
public class InscripcionEventoController {

    private final InscripcionEventoRepository inscripcionRepository;
    private final EventoRepository eventoRepository;
    private final NotificacionService notificacionService;

    @GetMapping("/api/inscripciones/mias")
    public List<InscripcionEvento> misInscripciones(@AuthenticationPrincipal User usuario) {
        return inscripcionRepository.findByUserId(usuario.getId());
    }

    @PostMapping("/api/inscripciones")
    public ResponseEntity<InscripcionEvento> inscribirse(
            @AuthenticationPrincipal User usuario,
            @Valid @RequestBody InscripcionEventoRequest datos
    ) {
        Evento evento = eventoRepository.findById(datos.eventoId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));

        if (evento.getEstado() != EstadoEvento.ACTIVO) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Este evento ya no está activo.");
        }
        if (evento.getFecha().isBefore(LocalDateTime.now())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Este evento ya pasó, no se puede inscribir.");
        }

        boolean yaInscrito = inscripcionRepository.findByUserId(usuario.getId()).stream()
                .anyMatch(i -> i.getEvento().getId().equals(evento.getId()) && i.getEstado() != EstadoInscripcion.CANCELADA);
        if (yaInscrito) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Ya estás inscrito a este evento.");
        }

        if (Boolean.TRUE.equals(datos.llevaInvitado())) {
            if (datos.nombreInvitado() == null || datos.nombreInvitado().isBlank()
                    || datos.correoInvitado() == null || datos.correoInvitado().isBlank()
                    || datos.cedulaInvitado() == null || datos.cedulaInvitado().isBlank()
                    || datos.tipoDocumentoInvitado() == null || datos.tipoDocumentoInvitado().isBlank()) {
                throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Completa todos los datos de tu invitado.");
            }
        }

        int solicitado = Boolean.TRUE.equals(datos.llevaInvitado()) ? 2 : 1;
        if (evento.getCapacidad() != null) {
            int ocupados = inscripcionRepository.findByEventoId(evento.getId()).stream()
                    .filter(i -> i.getEstado() != EstadoInscripcion.CANCELADA)
                    .mapToInt(InscripcionEvento::cantidadOcupada)
                    .sum();

            if (ocupados + solicitado > evento.getCapacidad()) {
                int disponibles = Math.max(0, evento.getCapacidad() - ocupados);
                throw new ResponseStatusException(HttpStatus.CONFLICT, "No hay cupos suficientes. Cupos disponibles: " + disponibles + ".");
            }
        }

        InscripcionEvento inscripcion = InscripcionEvento.builder()
                .user(usuario)
                .evento(evento)
                .llevaInvitado(datos.llevaInvitado())
                .nombreInvitado(datos.llevaInvitado() ? datos.nombreInvitado() : null)
                .correoInvitado(datos.llevaInvitado() ? datos.correoInvitado() : null)
                .cedulaInvitado(datos.llevaInvitado() ? datos.cedulaInvitado() : null)
                .tipoDocumentoInvitado(datos.llevaInvitado() ? datos.tipoDocumentoInvitado() : null)
                .estado(EstadoInscripcion.PENDIENTE)
                .build();

        InscripcionEvento guardada = inscripcionRepository.save(inscripcion);

        notificacionService.crear(
                usuario,
                "🎟️ Recibimos tu inscripción a \"" + evento.getTitulo() + "\". Te avisaremos en cuanto sea confirmada."
        );

        return ResponseEntity.ok(guardada);
    }

    // ── Admin ──
    @GetMapping("/api/admin/inscripciones")
    public List<InscripcionEvento> listarAdmin(@RequestParam(required = false) Long eventoId) {
        return eventoId != null ? inscripcionRepository.findByEventoId(eventoId) : inscripcionRepository.findAll();
    }

    @PostMapping("/api/admin/inscripciones/{id}/aceptar")
    public ResponseEntity<InscripcionEvento> aceptar(@PathVariable Long id) {
        InscripcionEvento inscripcion = obtenerPendienteOFallar(id);

        inscripcion.setEstado(EstadoInscripcion.CONFIRMADA);
        InscripcionEvento guardada = inscripcionRepository.save(inscripcion);

        notificacionService.crear(
                inscripcion.getUser(),
                "🎉 ¡Felicidades! Tu inscripción a \"" + inscripcion.getEvento().getTitulo() + "\" fue confirmada. ¡Te esperamos!"
        );

        return ResponseEntity.ok(guardada);
    }

    @PostMapping("/api/admin/inscripciones/{id}/rechazar")
    public ResponseEntity<InscripcionEvento> rechazar(@PathVariable Long id, @RequestBody Map<String, String> body) {
        String motivo = body.get("motivo");
        if (motivo == null || motivo.isBlank()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Debes indicar un motivo de rechazo.");
        }

        InscripcionEvento inscripcion = obtenerPendienteOFallar(id);

        inscripcion.setEstado(EstadoInscripcion.CANCELADA);
        inscripcion.setMotivoRechazo(motivo);
        InscripcionEvento guardada = inscripcionRepository.save(inscripcion);

        notificacionService.crear(
                inscripcion.getUser(),
                "Tu inscripción a \"" + inscripcion.getEvento().getTitulo() + "\" no fue confirmada. Motivo: " + motivo
        );

        return ResponseEntity.ok(guardada);
    }

    private InscripcionEvento obtenerPendienteOFallar(Long id) {
        InscripcionEvento inscripcion = inscripcionRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Inscripción no encontrada."));

        if (inscripcion.getEstado() != EstadoInscripcion.PENDIENTE) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Esta inscripción ya fue procesada anteriormente.");
        }
        return inscripcion;
    }
}