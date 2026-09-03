package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "solicitudes_adopcion")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class SolicitudAdopcion {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "mascota_id", nullable = false)
    private Mascota mascota;

    // ── Datos del solicitante ──
    @Column(name = "nombre_completo", nullable = false)
    private String nombreCompleto;

    @Column(nullable = false)
    private String cedula;

    private String telefono;
    private String direccion;
    private String ciudad;

    // ── Vivienda ──
    @Column(name = "tipo_vivienda")
    private String tipoVivienda; // "casa" | "apartamento"

    @Column(name = "tiene_patio")
    private Boolean tienePatio;

    @Column(name = "es_propia")
    private Boolean esPropia;

    // ── Convivencia ──
    @Column(name = "tiene_ninos")
    private Boolean tieneNinos;

    @Column(name = "edades_ninos")
    private String edadesNinos;

    @Column(name = "tiene_otros_animales")
    private Boolean tieneOtrosAnimales;

    @Column(name = "cuales_animales")
    private String cualesAnimales;

    @Column(name = "personas_en_casa")
    private Integer personasEnCasa;

    // ── Experiencia ──
    @Column(name = "tiene_experiencia")
    private Boolean tieneExperiencia;

    @Column(name = "descripcion_experiencia", length = 1000)
    private String descripcionExperiencia;

    @Column(name = "horas_sola_mascota")
    private Integer horasSolaMascota;

    @Column(name = "quien_cuida_ausencia")
    private String quienCuidaAusencia;

    @Column(name = "motivo_adopcion", length = 1000)
    private String motivoAdopcion;

    private Boolean compromiso;

    // ── Estado del proceso ──
    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoSolicitud estado = EstadoSolicitud.PENDIENTE;

    @Column(length = 500)
    private String observaciones; // motivo si se rechaza, notas del admin

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }
}