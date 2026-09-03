package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.CitaDonacionRequest;
import com.adoptfest.backend.model.CitaDonacion;
import com.adoptfest.backend.repository.CitaDonacionRepository;
import com.adoptfest.backend.service.CitaDonacionService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/admin/citas-donaciones")
@RequiredArgsConstructor
public class CitaDonacionController {

    private final CitaDonacionRepository citaDonacionRepository;
    private final CitaDonacionService citaDonacionService;

    @GetMapping
    public List<CitaDonacion> listar() {
        return citaDonacionRepository.findAll();
    }

    @PostMapping
    public ResponseEntity<CitaDonacion> agendar(@Valid @RequestBody CitaDonacionRequest datos) {
        return ResponseEntity.ok(citaDonacionService.agendar(datos));
    }

    @PatchMapping("/{id}/completar")
    public ResponseEntity<CitaDonacion> completar(@PathVariable Long id) {
        return ResponseEntity.ok(citaDonacionService.completar(id));
    }

    @PatchMapping("/{id}/cancelar")
    public ResponseEntity<CitaDonacion> cancelar(@PathVariable Long id) {
        return ResponseEntity.ok(citaDonacionService.cancelar(id));
    }
}