package com.adoptfest.backend.exception;

import com.adoptfest.backend.dto.ErrorResponse;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.server.ResponseStatusException;

import java.time.Instant;
import java.util.HashMap;
import java.util.Map;

@RestControllerAdvice
public class GlobalExceptionHandler {

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ErrorResponse> manejarValidacion(MethodArgumentNotValidException ex) {
        Map<String, String> errores = new HashMap<>();
        ex.getBindingResult().getFieldErrors().forEach(err -> errores.put(err.getField(), err.getDefaultMessage()));
        return ResponseEntity.badRequest().body(new ErrorResponse(Instant.now(), 400, "Datos inválidos.", errores));
    }

    @ExceptionHandler(ResponseStatusException.class)
    public ResponseEntity<ErrorResponse> manejarEstadoRespuesta(ResponseStatusException ex) {
        return ResponseEntity.status(ex.getStatusCode()).body(ErrorResponse.simple(ex.getStatusCode().value(), ex.getReason()));
    }
@ExceptionHandler(Exception.class)
public ResponseEntity<ErrorResponse> manejarGenerico(Exception ex) {
    ex.printStackTrace(); 
    return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(
            ErrorResponse.simple(500, "Ocurrió un error inesperado. Intenta de nuevo.")
    );
}
}