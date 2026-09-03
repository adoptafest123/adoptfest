// src/main/java/com/adoptfest/backend/controller/RefugioController.java
package com.adoptfest.backend.controller;

import com.adoptfest.backend.model.Refugio;
import com.adoptfest.backend.service.RefugioService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/refugios")
@RequiredArgsConstructor
public class RefugioController {

    private final RefugioService refugioService;

    // ── Público ──
    @GetMapping
    public List<Refugio> listarActivos() {
        return refugioService.listarActivos();
    }

    @GetMapping("/{id}")
    public Refugio obtener(@PathVariable Long id) {
        return refugioService.obtener(id);
    }

    // ── Admin ──
    @GetMapping("/admin/todos")
    public List<Refugio> listarTodos() {
        return refugioService.listarTodos();
    }

    @GetMapping("/admin/estadisticas")
    public Map<String, Object> obtenerEstadisticasTodos() {
        return refugioService.obtenerEstadisticasTodosRefugios();
    }

    @GetMapping("/admin/{id}/estadisticas")
    public Map<String, Object> obtenerEstadisticasRefugio(@PathVariable Long id) {
        return refugioService.obtenerEstadisticasRefugio(id);
    }

    @PostMapping("/admin")
    public ResponseEntity<Refugio> crear(@RequestBody Refugio refugio) {
        return ResponseEntity.ok(refugioService.crear(refugio));
    }

    @PutMapping("/admin/{id}")
    public ResponseEntity<Refugio> actualizar(@PathVariable Long id, @RequestBody Refugio refugioActualizado) {
        return ResponseEntity.ok(refugioService.actualizar(id, refugioActualizado));
    }

    @DeleteMapping("/admin/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Long id) {
        refugioService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}