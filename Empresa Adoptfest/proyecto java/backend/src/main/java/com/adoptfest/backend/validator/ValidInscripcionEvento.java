package com.adoptfest.backend.validator;

import jakarta.validation.Constraint;
import jakarta.validation.Payload;
import java.lang.annotation.*;

@Target({ElementType.TYPE})
@Retention(RetentionPolicy.RUNTIME)
@Constraint(validatedBy = InscripcionEventoValidator.class)
@Documented
public @interface ValidInscripcionEvento {
    String message() default "Validación fallida de inscripción a evento.";
    Class<?>[] groups() default {};
    Class<? extends Payload>[] payload() default {};
}
