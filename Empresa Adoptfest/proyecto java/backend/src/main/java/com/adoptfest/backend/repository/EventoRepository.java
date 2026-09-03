package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.Evento;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface EventoRepository extends JpaRepository<Evento, Long> {
    List<Evento> findAllByOrderByFechaDesc();

    // Equivalente al "where titulo like %x% or lugar like %x%" del admin
    List<Evento> findByTituloContainingIgnoreCaseOrLugarContainingIgnoreCase(String titulo, String lugar);
}