// src/main/java/com/adoptfest/backend/repository/RefugioRepository.java
package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Refugio;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface RefugioRepository extends JpaRepository<Refugio, Long> {
    List<Refugio> findByActivoTrueOrderByNombreAsc();
}