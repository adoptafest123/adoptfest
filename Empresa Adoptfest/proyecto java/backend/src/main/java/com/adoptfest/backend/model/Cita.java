package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDate;
import java.time.LocalTime;

@Entity
@Table(name = "citas")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Cita {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @OneToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "solicitud_id", nullable = false, unique = true)
    private SolicitudAdopcion solicitud;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "mascota_id", nullable = false)
    private Mascota mascota;

    @Column
    private LocalDate fecha;

    @Column
    private LocalTime hora;

    private String lugar;

    private String enlaceVirtual;

    @Column(name = "direccion_cita")
    private String direccionCita;

    @Column(length = 500)
    private String notas;

    @Column(name = "codigo_verificacion", unique = true)
    private String codigoVerificacion; // el código que el adoptante muestra el día de la cita

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoCita estado = EstadoCita.PROGRAMADA;

    @Builder.Default
    private Boolean verificada = false;
}