package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Cita;
import com.adoptfest.backend.model.EstadoCita;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;
import java.util.Optional;

public interface CitaRepository extends JpaRepository<Cita, Long> {
    List<Cita> findByEstado(EstadoCita estado);
    List<Cita> findByUserId(Long userId);
    Optional<Cita> findBySolicitudId(Long solicitudId);
    Optional<Cita> findByCodigoVerificacion(String codigo);
}