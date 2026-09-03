package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.AgendarCitaRequest;
import com.adoptfest.backend.model.Cita;
import com.adoptfest.backend.service.CitaService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/admin/citas")
@RequiredArgsConstructor
public class AdminCitaController {

    private final CitaService citaService;

    @GetMapping("/pendientes")
    public List<Cita> pendientes() {
        return citaService.pendientesDeAgendar();
    }

    @GetMapping("/agendadas")
    public List<Cita> agendadas() {
        return citaService.agendadas();
    }

    @PatchMapping("/{id}/agendar")
    public ResponseEntity<Cita> agendar(@PathVariable Long id, @Valid @RequestBody AgendarCitaRequest datos) {
        return ResponseEntity.ok(citaService.agendar(id, datos.fecha(), datos.hora(), datos.notas(), datos.enlaceVirtual()));
    }
}