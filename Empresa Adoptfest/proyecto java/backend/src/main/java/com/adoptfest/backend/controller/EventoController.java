package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.EventoRequest;
import com.adoptfest.backend.model.*;
import com.adoptfest.backend.repository.EventoRepository;
import com.adoptfest.backend.repository.InscripcionEventoRepository;
import com.adoptfest.backend.service.FileStorageService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDate;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@RequiredArgsConstructor
public class EventoController {

    private final EventoRepository eventoRepository;
    private final InscripcionEventoRepository inscripcionEventoRepository;
    private final FileStorageService fileStorageService;

    // ── Público ──
    @GetMapping("/api/eventos")
    public List<Evento> listarPublico() {
        return eventoRepository.findAllByOrderByFechaDesc();
    }

    @GetMapping("/api/eventos/{id}")
    public Evento obtener(@PathVariable Long id) {
        return eventoRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));
    }

    /** Cupos disponibles en vivo — igual concepto que /interes de mascotas. */
   @GetMapping("/api/eventos/{id}/cupos")
public Map<String, Object> cupos(@PathVariable Long id) {
    Evento evento = eventoRepository.findById(id)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));

    int ocupados = inscripcionEventoRepository.findByEventoId(id).stream()
            .filter(i -> i.getEstado() != EstadoInscripcion.CANCELADA)
            .mapToInt(InscripcionEvento::cantidadOcupada)
            .sum();

    Integer capacidad = evento.getCapacidad();
    Integer disponibles = capacidad == null ? null : Math.max(0, capacidad - ocupados);

    Map<String, Object> resultado = new HashMap<>();
    resultado.put("capacidad", capacidad);
    resultado.put("ocupados", ocupados);
    resultado.put("disponibles", disponibles);
    return resultado;
}

    // ── Admin ──
    @GetMapping("/api/admin/eventos")
    public List<Evento> listarAdmin(@RequestParam(required = false) String buscar) {
        if (buscar != null && !buscar.isBlank()) {
            return eventoRepository.findByTituloContainingIgnoreCaseOrLugarContainingIgnoreCase(buscar, buscar);
        }
        return eventoRepository.findAllByOrderByFechaDesc();
    }

    @PostMapping("/api/admin/eventos")
    public ResponseEntity<Evento> crear(@Valid @RequestBody EventoRequest datos) {
        validarFechaYHorario(datos);

        Evento evento = Evento.builder()
                .titulo(datos.titulo())
                .fecha(datos.fecha())
                .horaFin(datos.horaFin())
                .lugar(datos.lugar())
                .descripcion(datos.descripcion())
                .categoria(datos.categoria())
                .capacidad(datos.capacidad())
                .imagen(datos.imagen())
                .estado(EstadoEvento.ACTIVO)
                .build();

        return ResponseEntity.ok(eventoRepository.save(evento));
    }

    @PutMapping("/api/admin/eventos/{id}")
    public ResponseEntity<Evento> actualizar(@PathVariable Long id, @Valid @RequestBody EventoRequest datos) {
        Evento evento = eventoRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));

        validarFechaYHorario(datos);

        evento.setTitulo(datos.titulo());
        evento.setFecha(datos.fecha());
        evento.setHoraFin(datos.horaFin());
        evento.setLugar(datos.lugar());
        evento.setDescripcion(datos.descripcion());
        evento.setCategoria(datos.categoria());
        evento.setCapacidad(datos.capacidad());

        if (datos.imagen() != null && !datos.imagen().equals(evento.getImagen())) {
            fileStorageService.eliminar(evento.getImagen());
            evento.setImagen(datos.imagen());
        }

        return ResponseEntity.ok(eventoRepository.save(evento));
    }

    @PatchMapping("/api/admin/eventos/{id}/estado")
    public ResponseEntity<Evento> cambiarEstado(@PathVariable Long id, @RequestBody EstadoEvento nuevoEstado) {
        Evento evento = eventoRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));
        evento.setEstado(nuevoEstado);
        return ResponseEntity.ok(eventoRepository.save(evento));
    }

    @DeleteMapping("/api/admin/eventos/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Long id) {
        Evento evento = eventoRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Evento no encontrado."));
        fileStorageService.eliminar(evento.getImagen());
        eventoRepository.delete(evento);
        return ResponseEntity.noContent().build();
    }

    /** No fechas pasadas, y la hora de cierre debe ser posterior a la de inicio. */
    private void validarFechaYHorario(EventoRequest datos) {
        if (datos.fecha().toLocalDate().isBefore(LocalDate.now())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "La fecha del evento no puede ser en el pasado.");
        }
        if (!datos.horaFin().isAfter(datos.fecha().toLocalTime())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "La hora de cierre debe ser posterior a la hora de inicio.");
        }
    }
}