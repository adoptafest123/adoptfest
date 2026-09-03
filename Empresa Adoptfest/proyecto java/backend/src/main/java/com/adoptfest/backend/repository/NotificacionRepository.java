package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Notificacion;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface NotificacionRepository extends JpaRepository<Notificacion, Long> {
    List<Notificacion> findByUserIdOrderByCreatedAtDesc(Long userId);
    List<Notificacion> findByUserIdAndLeidaFalse(Long userId);
    long countByUserIdAndLeidaFalse(Long userId);
}