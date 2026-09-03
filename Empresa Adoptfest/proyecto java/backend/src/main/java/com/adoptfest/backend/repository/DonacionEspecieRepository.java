package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.DonacionEspecie;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface DonacionEspecieRepository extends JpaRepository<DonacionEspecie, Long> {
    List<DonacionEspecie> findByUserIdAndOcultoParaUsuarioFalseOrderByCreatedAtDesc(Long userId);
    List<DonacionEspecie> findAllByOrderByCreatedAtDesc();
}