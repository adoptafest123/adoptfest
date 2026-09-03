// src/main/java/com/adoptfest/backend/dto/CrearDonacionDineroRequest.java
package com.adoptfest.backend.dto;

import jakarta.validation.constraints.DecimalMax;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;

public record CrearDonacionDineroRequest(
        @NotNull(message = "Ingresa un monto a donar.")
        @DecimalMin(value = "1", message = "El monto mínimo de donación es $1.")
        @DecimalMax(value = "10000", message = "El monto máximo permitido es $10.000.")
        BigDecimal monto,

        Long refugioId
) {}