// src/main/java/com/adoptfest/backend/repository/DonacionDineroRepository.java
package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Cita;
import com.adoptfest.backend.model.DonacionDinero;
import com.adoptfest.backend.model.EstadoCita;
import com.adoptfest.backend.model.EstadoDonacionDinero;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;
import java.util.Optional;

public interface DonacionDineroRepository extends JpaRepository<DonacionDinero, Long> {
    List<DonacionDinero> findByUserIdAndOcultoParaUsuarioFalseOrderByCreatedAtDesc(Long userId);
    List<DonacionDinero> findByEstadoOrderByCreatedAtDesc(EstadoDonacionDinero estado);
    List<DonacionDinero> findAllByOrderByCreatedAtDesc();
    Optional<DonacionDinero> findByPaypalOrderId(String paypalOrderId);
    List<Cita> findByEstado(EstadoCita estado);
    
    // 👇 NUEVO MÉTODO PARA REFUGIOS
    List<DonacionDinero> findByRefugioId(Long refugioId);
}