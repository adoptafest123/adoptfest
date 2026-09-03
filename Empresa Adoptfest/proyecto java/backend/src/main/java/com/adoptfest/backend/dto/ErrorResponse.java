package com.adoptfest.backend.dto;

import java.time.Instant;
import java.util.Map;

public record ErrorResponse(
        Instant timestamp,
        int status,
        String mensaje,
        Map<String, String> errores
) {
    public static ErrorResponse simple(int status, String mensaje) {
        return new ErrorResponse(Instant.now(), status, mensaje, null);
    }
}