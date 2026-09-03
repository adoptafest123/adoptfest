package com.adoptfest.backend.dto;

import jakarta.validation.constraints.*;

public record RegistroRequest(
        @NotBlank(message = "El nombre es obligatorio.")
        @Size(min = 2, max = 100, message = "El nombre debe tener al menos 2 caracteres.")
        String nombre,

        @NotBlank(message = "El correo es obligatorio.")
        @Email(message = "Ingresa un correo válido.")
        @Size(max = 255)
        String correo,

        @NotBlank(message = "La cédula es obligatoria.")
        @Pattern(regexp = "^[0-9]{6,15}$", message = "La cédula debe tener entre 6 y 15 dígitos.")
        String cedula,

        @NotBlank(message = "El teléfono es obligatorio.")
        @Pattern(regexp = "^[0-9]{7,20}$", message = "El teléfono debe tener entre 7 y 20 dígitos.")
        String telefono,

        @NotBlank(message = "La contraseña es obligatoria.")
        @Size(min = 6, max = 255, message = "La contraseña debe tener al menos 6 caracteres.")
        String contrasena
) {}