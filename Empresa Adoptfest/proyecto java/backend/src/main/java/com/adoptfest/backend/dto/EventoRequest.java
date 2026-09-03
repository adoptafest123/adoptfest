package com.adoptfest.backend.dto;

import com.adoptfest.backend.model.CategoriaEvento;
import jakarta.validation.constraints.*;
import java.time.LocalDateTime;
import java.time.LocalTime;

public record EventoRequest(
        @NotBlank(message = "El título es obligatorio.")
        @Size(min = 3, max = 200, message = "El título debe tener entre 3 y 200 caracteres.")
        String titulo,

        @NotNull(message = "La fecha y hora de inicio son obligatorias.")
        LocalDateTime fecha,

        @NotNull(message = "La hora de cierre es obligatoria.")
        LocalTime horaFin,

        @Size(max = 100, message = "El lugar no puede superar 100 caracteres.")
        String lugar,

        @Size(max = 500, message = "La descripción no puede superar 500 caracteres.")
        String descripcion,

        @NotNull(message = "Selecciona una categoría.")
        CategoriaEvento categoria,

        @Min(value = 1, message = "La capacidad debe ser al menos 1.")
        Integer capacidad,

        String imagen
) {}