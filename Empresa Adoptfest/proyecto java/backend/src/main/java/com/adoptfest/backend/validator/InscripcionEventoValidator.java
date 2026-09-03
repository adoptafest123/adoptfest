package com.adoptfest.backend.validator;

import jakarta.validation.ConstraintValidator;
import jakarta.validation.ConstraintValidatorContext;
import com.adoptfest.backend.dto.InscripcionEventoRequest;

public class InscripcionEventoValidator implements ConstraintValidator<ValidInscripcionEvento, InscripcionEventoRequest> {

    @Override
    public void initialize(ValidInscripcionEvento annotation) {
    }

    @Override
    public boolean isValid(InscripcionEventoRequest request, ConstraintValidatorContext context) {
        if (request == null) {
            return true;
        }

        // Si NO lleva invitado, no necesita validar los campos de invitado
        if (!Boolean.TRUE.equals(request.llevaInvitado())) {
            return true;
        }

        // Si LLEVA invitado, validar todos los campos
        boolean valido = true;

        if (request.nombreInvitado() == null || request.nombreInvitado().isBlank()) {
            agregarViolacion(context, "nombreInvitado", "El nombre del invitado es obligatorio.");
            valido = false;
        }

        if (request.correoInvitado() == null || !request.correoInvitado().contains("@")) {
            agregarViolacion(context, "correoInvitado", "El correo del invitado no es válido.");
            valido = false;
        }

        if (request.cedulaInvitado() == null || !request.cedulaInvitado().matches("^[0-9]{6,15}$")) {
            agregarViolacion(context, "cedulaInvitado", "El documento del invitado debe tener entre 6 y 15 dígitos.");
            valido = false;
        }

        if (request.tipoDocumentoInvitado() == null || request.tipoDocumentoInvitado().isBlank()) {
            agregarViolacion(context, "tipoDocumentoInvitado", "El tipo de documento del invitado es obligatorio.");
            valido = false;
        }

        return valido;
    }

    private void agregarViolacion(ConstraintValidatorContext context, String propiedad, String mensaje) {
        context.buildConstraintViolationWithTemplate(mensaje)
               .addPropertyNode(propiedad)
               .addConstraintViolation();
    }
}
