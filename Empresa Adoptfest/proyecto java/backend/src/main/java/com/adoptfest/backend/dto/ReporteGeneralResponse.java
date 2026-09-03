package com.adoptfest.backend.dto;

import java.math.BigDecimal;
import java.util.Map;

public record ReporteGeneralResponse(
        long totalMascotas,
        Map<String, Long> mascotasPorEstado,       // {"DISPONIBLE": 12, "ADOPTADO": 34, ...}
        long totalAdopcionesCompletadas,
        long solicitudesPendientes,
        long totalEventos,
        long totalInscripcionesEventos,
        BigDecimal totalRecaudadoDinero,
        long totalDonacionesEspecieConfirmadas,
        long totalUsuarios
) {}