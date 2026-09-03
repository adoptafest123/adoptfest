package com.adoptfest.backend.service;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.server.ResponseStatusException;
import org.springframework.http.HttpStatus;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.List;
import java.util.UUID;

/**
 * Reemplaza el patrón que repetías en cada controlador de Laravel
 * (moverImagen() / eliminarImagen() copiado y pegado en EventoController,
 * MascotaController, etc). Aquí se escribe una sola vez y todos los
 * módulos lo reutilizan.
 */
@Service
public class FileStorageService {

    private static final List<String> TIPOS_PERMITIDOS =
            List.of("image/jpeg", "image/png", "image/webp", "image/gif");
    private static final long TAMANO_MAXIMO = 5 * 1024 * 1024; // 5MB, igual que Laravel

    @Value("${app.upload.dir}")
    private String uploadDir;

    public String guardar(MultipartFile archivo) {
        if (archivo == null || archivo.isEmpty()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "No se recibió ninguna imagen.");
        }
        if (!TIPOS_PERMITIDOS.contains(archivo.getContentType())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Solo se permiten imágenes JPG, PNG, WEBP o GIF.");
        }
        if (archivo.getSize() > TAMANO_MAXIMO) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "La imagen no puede pesar más de 5MB.");
        }

        try {
            Path carpeta = resolverDirectorioUploads();
            if (!Files.exists(carpeta)) {
                Files.createDirectories(carpeta);
            }

            String extension = obtenerExtension(archivo.getOriginalFilename());
            String nombreUnico = UUID.randomUUID() + extension;

            Path destino = carpeta.resolve(nombreUnico);
            Files.copy(archivo.getInputStream(), destino);

            return "/uploads/" + nombreUnico; // esta es la URL que React va a guardar
        } catch (IOException e) {
            throw new ResponseStatusException(HttpStatus.INTERNAL_SERVER_ERROR, "No se pudo guardar la imagen.");
        }
    }

    public void eliminar(String urlImagen) {
        if (urlImagen == null || urlImagen.isBlank()) return;
        try {
            String nombreArchivo = urlImagen.substring(urlImagen.lastIndexOf("/") + 1);
            Files.deleteIfExists(resolverDirectorioUploads().resolve(nombreArchivo));
        } catch (IOException ignored) {
            // Si falla borrar el archivo viejo no es crítico, seguimos.
        }
    }

    private String obtenerExtension(String nombreOriginal) {
        if (nombreOriginal == null || !nombreOriginal.contains(".")) return "";
        return nombreOriginal.substring(nombreOriginal.lastIndexOf("."));
    }

    private Path resolverDirectorioUploads() {
        try {
            Path clases = Paths.get(FileStorageService.class.getProtectionDomain()
                    .getCodeSource().getLocation().toURI());
            return clases.getParent().getParent().resolve(uploadDir).normalize();
        } catch (Exception ignored) {
            return Paths.get(uploadDir).toAbsolutePath().normalize();
        }
    }
}