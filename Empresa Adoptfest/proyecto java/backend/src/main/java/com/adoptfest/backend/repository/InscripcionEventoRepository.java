package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.InscripcionEvento;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface InscripcionEventoRepository extends JpaRepository<InscripcionEvento, Long> {
    List<InscripcionEvento> findByUserId(Long userId);
    List<InscripcionEvento> findByEventoId(Long eventoId);
}