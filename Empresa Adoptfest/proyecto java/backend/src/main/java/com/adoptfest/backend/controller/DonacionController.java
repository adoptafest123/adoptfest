package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.CrearDonacionDineroRequest;
import com.adoptfest.backend.dto.DonacionEspecieRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.DonacionDineroRepository;
import com.adoptfest.backend.repository.DonacionEspecieRepository;
import com.adoptfest.backend.service.DonacionService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Map;

@RestController
@RequiredArgsConstructor
public class DonacionController {

    private final DonacionService donacionService;
    private final DonacionDineroRepository donacionDineroRepository;
    private final DonacionEspecieRepository donacionEspecieRepository;

    @Value("${app.paypal.currency}")
    private String monedaDefault;

    // ══════════ Cliente: dinero ══════════

    @PostMapping("/api/donaciones/dinero/crear-orden")
    public ResponseEntity<Map<String, Object>> crearOrdenDinero(
            @AuthenticationPrincipal User usuario,
            @Valid @RequestBody CrearDonacionDineroRequest datos
    ) {
        return ResponseEntity.ok(donacionService.crearOrdenDinero(usuario, datos, monedaDefault));
    }

    @PostMapping("/api/donaciones/dinero/confirmar")
    public ResponseEntity<DonacionDinero> confirmarDinero(@RequestBody Map<String, String> body) {
        return ResponseEntity.ok(donacionService.confirmarPagoDinero(body.get("orderId")));
    }

    @PostMapping("/api/donaciones/dinero/cancelar")
    public ResponseEntity<Void> cancelarDinero(@RequestBody Map<String, String> body) {
        donacionService.cancelarDinero(body.get("orderId"));
        return ResponseEntity.noContent().build();
    }

    // ══════════ Cliente: especie ══════════

    @PostMapping("/api/donaciones/especie")
    public ResponseEntity<DonacionEspecie> registrarEspecie(
            @AuthenticationPrincipal User usuario,
            @Valid @RequestBody DonacionEspecieRequest datos
    ) {
        return ResponseEntity.ok(donacionService.registrarEspecie(usuario, datos));
    }

    // ══════════ Cliente: mis donaciones ══════════

    @GetMapping("/api/donaciones/mias")
    public Map<String, Object> misDonaciones(@AuthenticationPrincipal User usuario) {
        List<DonacionDinero> dinero = donacionDineroRepository
                .findByUserIdAndOcultoParaUsuarioFalseOrderByCreatedAtDesc(usuario.getId());
        List<DonacionEspecie> especie = donacionEspecieRepository
                .findByUserIdAndOcultoParaUsuarioFalseOrderByCreatedAtDesc(usuario.getId());

        return Map.of("dinero", dinero, "especie", especie);
    }

    // ══════════ Admin ══════════

    @GetMapping("/api/admin/donaciones")
    public Map<String, Object> listarAdmin() {
        List<DonacionEspecie> especies = donacionEspecieRepository.findAllByOrderByCreatedAtDesc();
        // 👇 CAMBIADO: ahora trae TODAS las donaciones en dinero, no solo las completadas
        List<DonacionDinero> dineros = donacionDineroRepository.findAllByOrderByCreatedAtDesc();
        return Map.of("especies", especies, "dineros", dineros);
    }

    @PostMapping("/api/admin/donaciones/especie/{id}/aceptar")
    public ResponseEntity<DonacionEspecie> aceptarEspecie(@PathVariable Long id) {
        return ResponseEntity.ok(donacionService.aceptarEspecie(id));
    }

    @PostMapping("/api/admin/donaciones/especie/{id}/rechazar")
    public ResponseEntity<DonacionEspecie> rechazarEspecie(@PathVariable Long id) {
        return ResponseEntity.ok(donacionService.rechazarEspecie(id));
    }

    @PostMapping("/api/admin/donaciones/especie/{id}/confirmar")
    public ResponseEntity<DonacionEspecie> confirmarEspecie(@PathVariable Long id) {
        return ResponseEntity.ok(donacionService.confirmarEspecie(id));
    }

    @DeleteMapping("/api/admin/donaciones/especie/{id}")
    public ResponseEntity<Void> eliminarEspecie(@PathVariable Long id) {
        donacionService.eliminarEspecie(id);
        return ResponseEntity.noContent().build();
    }
}