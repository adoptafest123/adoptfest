package com.adoptfest.backend.dto;

import jakarta.validation.constraints.NotNull;
import java.time.LocalDate;
import java.time.LocalTime;

public record CitaDonacionRequest(
        @NotNull(message = "Falta indicar la donación.") Long donacionEspecieId,
        @NotNull(message = "La fecha es obligatoria.") LocalDate fecha,
        @NotNull(message = "La hora es obligatoria.") LocalTime hora,
        String notas
) {}