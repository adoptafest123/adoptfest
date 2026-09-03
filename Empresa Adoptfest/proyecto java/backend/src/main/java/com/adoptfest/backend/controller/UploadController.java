package com.adoptfest.backend.controller;

import com.adoptfest.backend.service.FileStorageService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;

import java.util.Map;

/**
 * Un único endpoint de subida, reutilizado por mascotas, eventos, etc.
 * Requiere estar logueado (regla general en SecurityConfig: anyRequest().authenticated()),
 * así que solo usuarios con sesión válida pueden subir archivos.
 */
@RestController
@RequiredArgsConstructor
public class UploadController {

    private final FileStorageService fileStorageService;

    @PostMapping("/api/uploads/imagen")
    public ResponseEntity<Map<String, String>> subirImagen(@RequestParam("archivo") MultipartFile archivo) {
        String url = fileStorageService.guardar(archivo);
        return ResponseEntity.ok(Map.of("url", url));
    }
}