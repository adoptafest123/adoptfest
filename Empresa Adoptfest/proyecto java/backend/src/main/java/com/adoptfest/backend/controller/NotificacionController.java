package com.adoptfest.backend.controller;

import com.adoptfest.backend.model.Notificacion;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.repository.NotificacionRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/notificaciones")
@RequiredArgsConstructor
public class NotificacionController {

    private final NotificacionRepository notificacionRepository;

    @GetMapping
    public List<Notificacion> listar(@AuthenticationPrincipal User usuario) {
        return notificacionRepository.findByUserIdOrderByCreatedAtDesc(usuario.getId());
    }

    @GetMapping("/no-leidas/contador")
    public Map<String, Long> contarNoLeidas(@AuthenticationPrincipal User usuario) {
        return Map.of("total", notificacionRepository.countByUserIdAndLeidaFalse(usuario.getId()));
    }

    @PatchMapping("/{id}/leida")
    public ResponseEntity<Notificacion> marcarLeida(@PathVariable Long id, @AuthenticationPrincipal User usuario) {
        Notificacion notif = obtenerPropia(id, usuario);

        notif.setLeida(true);
        return ResponseEntity.ok(notificacionRepository.save(notif));
    }

    @PatchMapping("/todas/leidas")
    public ResponseEntity<Void> marcarTodasLeidas(@AuthenticationPrincipal User usuario) {
        List<Notificacion> pendientes = notificacionRepository.findByUserIdAndLeidaFalse(usuario.getId());
        pendientes.forEach(notificacion -> notificacion.setLeida(true));
        notificacionRepository.saveAll(pendientes);
        return ResponseEntity.noContent().build();
    }

    @DeleteMapping("/todas")
    public ResponseEntity<Void> eliminarTodas(@AuthenticationPrincipal User usuario) {
        List<Notificacion> todas = notificacionRepository.findByUserIdOrderByCreatedAtDesc(usuario.getId());
        notificacionRepository.deleteAll(todas);
        return ResponseEntity.noContent().build();
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Long id, @AuthenticationPrincipal User usuario) {
        Notificacion notif = obtenerPropia(id, usuario);
        notificacionRepository.delete(notif);
        return ResponseEntity.noContent().build();
    }

    private Notificacion obtenerPropia(Long id, User usuario) {
        Notificacion notif = notificacionRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Notificación no encontrada."));
        if (!notif.getUser().getId().equals(usuario.getId())) {
            throw new ResponseStatusException(HttpStatus.FORBIDDEN, "No puedes modificar notificaciones de otro usuario.");
        }
        return notif;
    }
}