package com.adoptfest.backend.dto;

import com.adoptfest.backend.model.Rol;
import jakarta.validation.constraints.*;

public record AdminUserRequest(
        @NotBlank(message = "El nombre es obligatorio.")
        @Size(min = 2, max = 100)
        String nombre,

        @NotBlank(message = "El correo es obligatorio.")
        @Email(message = "Ingresa un correo válido.")
        String correo,

        @Pattern(regexp = "^$|^[0-9]{6,15}$", message = "La cédula debe tener entre 6 y 15 dígitos.")
        String cedula,

        @NotBlank(message = "El teléfono es obligatorio.")
        @Pattern(regexp = "^[0-9]{7,20}$", message = "Teléfono inválido.")
        String telefono,

        String contrasena,

        @NotNull(message = "Selecciona un rol.")
        Rol rol
) {}