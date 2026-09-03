package com.adoptfest.backend.dto;

import com.adoptfest.backend.model.EstadoMascota;
import jakarta.validation.constraints.*;

public record MascotaRequest(
        @NotBlank(message = "El nombre es obligatorio.")
        @Size(min = 2, max = 60, message = "El nombre debe tener entre 2 y 60 caracteres.")
        String nombre,

        @NotBlank(message = "Selecciona el tipo de mascota.")
        String tipo,

        @NotNull(message = "La edad es obligatoria.")
        @Min(value = 1, message = "La edad mínima es 1 año.")
        @Max(value = 10, message = "La edad máxima es 10 años.")
        Integer edad,

        @NotBlank(message = "La raza es obligatoria.")
        @Size(max = 60)
        String raza,

        @NotNull(message = "El peso es obligatorio.")
        @DecimalMin(value = "0.1", message = "El peso debe ser mayor a 0.")
        @DecimalMax(value = "120", message = "Revisa el peso, parece muy alto.")
        Double peso,

        @NotBlank(message = "La estatura es obligatoria.")
        @Size(max = 30)
        String estatura,

        @NotBlank(message = "La descripción es obligatoria.")
        @Size(min = 10, max = 500, message = "La descripción debe tener al menos 10 caracteres.")
        String descripcion,

        @NotBlank(message = "Debes subir una foto de la mascota.")
        String imagen,

        @NotNull(message = "Selecciona el estado.")
        EstadoMascota estado,

        @NotNull(message = "Indica si está vacunado.")
        Boolean vacunado,

        @NotNull(message = "Indica si está esterilizado.")
        Boolean esterilizado
) {}