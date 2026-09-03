package com.adoptfest.backend.dto;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import java.time.LocalDate;
import java.time.LocalTime;

public record AgendarCitaRequest(
        @NotNull(message = "La fecha es obligatoria.") LocalDate fecha,
        @NotNull(message = "La hora es obligatoria.") LocalTime hora,
        @Size(max = 300) String notas,
        @Size(max = 300) String enlaceVirtual
) {}