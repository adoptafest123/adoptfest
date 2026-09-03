package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.AdminUserRequest;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.service.AdminUserService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/admin/users")
@RequiredArgsConstructor
public class AdminUserController {

    private final AdminUserService adminUserService;

    @GetMapping
    public List<User> listar(@RequestParam(required = false) String buscar) {
        return adminUserService.listar(buscar);
    }

    @PostMapping
    public ResponseEntity<User> crear(@Valid @RequestBody AdminUserRequest datos) {
        return ResponseEntity.ok(adminUserService.crear(datos));
    }

   @PutMapping("/{id}")
public ResponseEntity<User> actualizar( @PathVariable Long id, @Valid @RequestBody AdminUserRequest datos, @AuthenticationPrincipal User admin
) {
    return ResponseEntity.ok(adminUserService.actualizar(id, datos, admin.getId()));
}
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Long id, @AuthenticationPrincipal User admin) {
        adminUserService.eliminar(id, admin.getId());
        return ResponseEntity.noContent().build();
    }
}