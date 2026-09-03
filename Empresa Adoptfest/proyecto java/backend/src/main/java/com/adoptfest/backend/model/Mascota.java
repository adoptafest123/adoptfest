package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "mascotas")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Mascota {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, unique = true)
    private String codigo;

    @Column(nullable = false)
    private String nombre;

    @Column(nullable = false)
    private String tipo; // "perro" | "gato"

    private Integer edad;

    private String raza;

    private Double peso; // en kg

    private String estatura; // ej: "35 cm" — texto libre, admite rangos como "30-40 cm"

    @Column(length = 500)
    private String descripcion;

    private String imagen;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoMascota estado = EstadoMascota.DISPONIBLE;

    @Column(nullable = false)
    @Builder.Default
    private Boolean vacunado = false;

    @Column(nullable = false)
    @Builder.Default
    private Boolean esterilizado = false;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        updatedAt = LocalDateTime.now();
    }
}