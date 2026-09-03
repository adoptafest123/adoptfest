package com.adoptfest.backend.dto;

import jakarta.validation.constraints.NotBlank;

public record LoginRequest(
        @NotBlank(message = "Ingresa tu correo o teléfono.") String identificador,
        @NotBlank(message = "Ingresa tu contraseña.") String contrasena
) {}