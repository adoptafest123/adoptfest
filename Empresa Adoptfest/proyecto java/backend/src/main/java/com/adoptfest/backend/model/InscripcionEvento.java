package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "inscripciones_eventos")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class InscripcionEvento {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "evento_id", nullable = false)
    private Evento evento;

    @Builder.Default
    private Boolean llevaInvitado = false;

    private String nombreInvitado;
    private String correoInvitado;
    private String cedulaInvitado;
    private String tipoDocumentoInvitado;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoInscripcion estado = EstadoInscripcion.PENDIENTE;

    @Column(length = 300)
    private String motivoRechazo;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }

    /** Cuántos cupos ocupa esta inscripción: 1 si va solo, 2 si lleva invitado. */
    public int cantidadOcupada() {
        return Boolean.TRUE.equals(llevaInvitado) ? 2 : 1;
    }
}