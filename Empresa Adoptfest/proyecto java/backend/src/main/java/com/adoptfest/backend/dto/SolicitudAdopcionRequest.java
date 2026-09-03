package com.adoptfest.backend.dto;

import jakarta.validation.constraints.*;

public record SolicitudAdopcionRequest(
        @NotNull(message = "Falta indicar la mascota.") Long mascotaId,

        @NotBlank(message = "El nombre completo es obligatorio.")
        String nombreCompleto,

        @NotBlank(message = "La cédula es obligatoria.")
        @Pattern(regexp = "^[0-9]{6,15}$", message = "La cédula debe tener entre 6 y 15 dígitos.")
        String cedula,

        @NotBlank(message = "El teléfono es obligatorio.")
        @Pattern(regexp = "^[0-9]{7,20}$", message = "Ingresa un teléfono válido.")
        String telefono,

        @NotBlank(message = "La dirección es obligatoria.") String direccion,
        @NotBlank(message = "La ciudad es obligatoria.") String ciudad,

        @NotBlank(message = "Indica el tipo de vivienda.") String tipoVivienda,
        Boolean tienePatio,
        Boolean esPropia,

        Boolean tieneNinos,
        String edadesNinos,
        Boolean tieneOtrosAnimales,
        String cualesAnimales,

        @NotNull(message = "Indica cuántas personas viven en casa.")
        @Min(value = 1, message = "Debe ser al menos 1.")
        Integer personasEnCasa,

        Boolean tieneExperiencia,
        String descripcionExperiencia,

        @NotNull(message = "Indica cuántas horas quedaría sola la mascota.")
        @Min(0) @Max(24)
        Integer horasSolaMascota,

        String quienCuidaAusencia,

        @NotBlank(message = "Cuéntanos tu motivo de adopción.")
        @Size(min = 10, message = "Danos un poco más de detalle (mínimo 10 caracteres).")
        String motivoAdopcion,

        @AssertTrue(message = "Debes aceptar el compromiso de adopción responsable.")
        Boolean compromiso
) {}