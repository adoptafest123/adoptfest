package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.CitaDonacion;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface CitaDonacionRepository extends JpaRepository<CitaDonacion, Long> {
    Optional<CitaDonacion> findByDonacionEspecieId(Long donacionEspecieId);
}