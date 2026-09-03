package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.SolicitudAdopcionRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.SolicitudAdopcionRepository;
import com.adoptfest.backend.service.AdopcionService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;
import java.util.Map;

@RestController
@RequiredArgsConstructor
public class AdopcionController {

    private final SolicitudAdopcionRepository solicitudRepository;
    private final AdopcionService adopcionService;
    
@GetMapping("/api/solicitudes-adopcion/mascota/{mascotaId}/mia")
public Map<String, Boolean> yaSolicite(@PathVariable Long mascotaId, @AuthenticationPrincipal User usuario) {
    boolean tieneSolicitud = solicitudRepository
            .existsByUserIdAndMascotaIdAndEstado(usuario.getId(), mascotaId, EstadoSolicitud.PENDIENTE);
    return Map.of("yaSolicite", tieneSolicitud);
}
    // ── Cliente ──
    @PostMapping("/api/solicitudes-adopcion")
    public ResponseEntity<Void> solicitar(
            @AuthenticationPrincipal User usuario,
            @Valid @RequestBody SolicitudAdopcionRequest datos
    ) {
        adopcionService.crearSolicitud(usuario, datos);
        return ResponseEntity.status(HttpStatus.CREATED).build();
    }

    @GetMapping("/api/solicitudes-adopcion/mias")
    public List<SolicitudAdopcion> misSolicitudes(@AuthenticationPrincipal User usuario) {
        return solicitudRepository.findByUserId(usuario.getId());
    }

    // ── Admin ──
    @GetMapping("/api/admin/solicitudes-adopcion")
    public List<SolicitudAdopcion> listarAdmin(@RequestParam(required = false) EstadoSolicitud estado) {
        return estado != null ? solicitudRepository.findByEstado(estado) : solicitudRepository.findAll();
    }

    @PostMapping("/api/admin/solicitudes-adopcion/{id}/aprobar")
    public ResponseEntity<Cita> aprobar(@PathVariable Long id) {
        return ResponseEntity.ok(adopcionService.aprobarYGenerarCita(id, null, null, null, null));
    }

    @PostMapping("/api/admin/solicitudes-adopcion/{id}/rechazar")
    public ResponseEntity<SolicitudAdopcion> rechazar(@PathVariable Long id, @RequestBody Map<String, String> body) {
        return ResponseEntity.ok(adopcionService.rechazar(id, body.get("motivo")));
    }

    // ── Verificación de cita el día de la entrega (recepcionista/admin escanea o digita el código) ──
    @PostMapping("/api/admin/citas/verificar")
    public ResponseEntity<Cita> verificarCita(@RequestBody Map<String, String> body) {
        String codigo = body.get("codigo");
        if (codigo == null || codigo.isBlank()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Falta el código de verificación.");
        }
        return ResponseEntity.ok(adopcionService.verificarCita(codigo));
    }
}