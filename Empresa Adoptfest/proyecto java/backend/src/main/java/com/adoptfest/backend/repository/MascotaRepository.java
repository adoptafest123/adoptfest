package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Mascota;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface MascotaRepository extends JpaRepository<Mascota, Long> {
    boolean existsByCodigo(String codigo);
    Optional<Mascota> findByCodigo(String codigo);
}
