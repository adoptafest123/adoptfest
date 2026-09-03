// src/main/java/com/adoptfest/backend/dto/DonacionEspecieRequest.java
package com.adoptfest.backend.dto;

import com.adoptfest.backend.model.CategoriaDonacion;
import com.adoptfest.backend.model.EspecieDestino;
import jakarta.validation.constraints.*;

public record DonacionEspecieRequest(
        @NotNull(message = "Selecciona qué tipo de insumo vas a donar.")
        CategoriaDonacion categoria,

        @NotNull(message = "Indica para qué especie es la donación.")
        EspecieDestino especieDestino,

        @Size(max = 255)
        String descripcion,

        @NotNull(message = "Indica la cantidad.")
        @Min(value = 1, message = "La cantidad mínima es 1.")
        @Max(value = 50, message = "La cantidad máxima por registro es 50.")
        Integer cantidad,

        @NotBlank(message = "Indica la dirección donde recogeremos la donación.")
        @Size(min = 10, max = 255, message = "Escribe una dirección más detallada (mínimo 10 caracteres).")
        String direccionRecoleccion,

        @NotBlank(message = "Indica un teléfono de contacto.")
        @Pattern(regexp = "^[0-9]{7,10}$", message = "El teléfono debe tener entre 7 y 10 dígitos, solo números.")
        String telefonoContacto,

        Long refugioId
) {}