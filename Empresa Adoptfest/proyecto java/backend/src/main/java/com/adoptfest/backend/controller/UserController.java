// src/main/java/com/adoptfest/backend/controller/UserController.java
package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.ActualizarPerfilRequest;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.repository.UserRepository;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/users")
@RequiredArgsConstructor
public class UserController {

    private final UserRepository userRepository;

    @GetMapping("/me")
    public User perfilActual(@AuthenticationPrincipal User usuario) {
        return usuario;
    }

    @PutMapping("/me/perfil")
    public ResponseEntity<User> actualizarPerfil(
            @AuthenticationPrincipal User usuario,
            @Valid @RequestBody ActualizarPerfilRequest datos
    ) {
        if (datos.nombre() != null && !datos.nombre().isBlank()) {
            usuario.setName(datos.nombre());
        }
        if (datos.telefono() != null && !datos.telefono().isBlank()) {
            usuario.setTelefono(datos.telefono());
        }
        // 👇 ELIMINAMOS la actualización de la cédula
        // if (datos.cedula() != null && !datos.cedula().isBlank()) {
        //     usuario.setCedula(datos.cedula());
        // }
        if (datos.foto() != null && !datos.foto().isBlank()) {
            usuario.setFoto(datos.foto());
        }
        if (datos.descripcion() != null) {
            usuario.setDescripcion(datos.descripcion());
        }

        return ResponseEntity.ok(userRepository.save(usuario));
    }
}