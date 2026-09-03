package com.adoptfest.backend.dto;

import jakarta.validation.constraints.*;
import com.adoptfest.backend.validator.ValidInscripcionEvento;

@ValidInscripcionEvento
public record InscripcionEventoRequest(
        @NotNull(message = "Falta indicar el evento.")
        Long eventoId,

        @NotNull(message = "Indica si llevarás un invitado.")
        Boolean llevaInvitado,

        String nombreInvitado,

        String correoInvitado,

        String cedulaInvitado,

        String tipoDocumentoInvitado,

        @NotNull(message = "Debes indicar si aceptas las reglas.")
        @AssertTrue(message = "Debes aceptar las reglas del evento.")
        Boolean aceptaReglas
) {}