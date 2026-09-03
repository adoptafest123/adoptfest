package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.EstadoSolicitud;
import com.adoptfest.backend.model.SolicitudAdopcion;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface SolicitudAdopcionRepository extends JpaRepository<SolicitudAdopcion, Long> {
    List<SolicitudAdopcion> findByUserId(Long userId);
    List<SolicitudAdopcion> findByEstado(EstadoSolicitud estado);
    boolean existsByUserIdAndMascotaIdAndEstado(Long userId, Long mascotaId, EstadoSolicitud estado);
    long countByMascotaIdAndEstado(Long mascotaId, EstadoSolicitud estado);
List<SolicitudAdopcion> findByMascotaIdAndEstado(Long mascotaId, EstadoSolicitud estado);
}