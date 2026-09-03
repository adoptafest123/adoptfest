package com.adoptfest.backend.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "donaciones_dinero")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DonacionDinero {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @Column(nullable = false, precision = 10, scale = 2)
    private BigDecimal monto; // BigDecimal, nunca double/float para dinero: evita errores de redondeo

    @Column(nullable = false)
    private String moneda;

    @Column(name = "paypal_order_id", unique = true)
    private String paypalOrderId;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    @Builder.Default
    private EstadoDonacionDinero estado = EstadoDonacionDinero.PENDIENTE;

    @Column(name = "puntos_otorgados")
    private Integer puntosOtorgados;

    @Column(name = "oculto_para_usuario", nullable = false)
    @Builder.Default
    private Boolean ocultoParaUsuario = false;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }
}