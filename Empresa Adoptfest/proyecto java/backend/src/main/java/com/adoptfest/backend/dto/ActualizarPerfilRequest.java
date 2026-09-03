// src/main/java/com/adoptfest/backend/dto/ActualizarPerfilRequest.java
package com.adoptfest.backend.dto;

import jakarta.validation.constraints.Pattern;
import jakarta.validation.constraints.Size;

public record ActualizarPerfilRequest(
        @Size(min = 2, max = 100, message = "El nombre debe tener al menos 2 caracteres.")
        String nombre,

        @Pattern(regexp = "^$|^[0-9]{7,20}$", message = "El teléfono debe tener entre 7 y 20 dígitos.")
        String telefono,

        // 👇 ELIMINAMOS la cédula del DTO para que no se pueda actualizar
        // String cedula,

        String foto, // URL de /api/uploads/imagen, o null si no cambia

        @Size(max = 300, message = "La descripción no puede superar 300 caracteres.")
        String descripcion
) {}