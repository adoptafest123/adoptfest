package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "donaciones_especie")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DonacionEspecie {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private CategoriaDonacion categoria;

    @Enumerated(EnumType.STRING)
    @Column(name = "especie_destino", nullable = false)
    private EspecieDestino especieDestino;

    @Column(length = 255)
    private String descripcion;

    @Column(nullable = false)
    private Integer cantidad;

    @Column(name = "direccion_recoleccion", nullable = false)
    private String direccionRecoleccion;

    @Column(name = "telefono_contacto", nullable = false)
    private String telefonoContacto;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoDonacionEspecie estado = EstadoDonacionEspecie.PENDIENTE;

    @Column(name = "puntos_otorgados")
    private Integer puntosOtorgados;

    @Column(name = "oculto_para_usuario", nullable = false)
    @Builder.Default
    private Boolean ocultoParaUsuario = false;

    @Column(name = "confirmado_at")
    private LocalDateTime confirmadoAt;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }

    /** Misma fórmula que tenías en Laravel: puntos por unidad × cantidad (mínimo 1). */
    public int calcularPuntos() {
        return categoria.getPuntosPorUnidad() * Math.max(1, cantidad);
    }
}