// src/main/java/com/adoptfest/backend/repository/UserRepository.java
package com.adoptfest.backend.repository;

import com.adoptfest.backend.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.Optional;

public interface UserRepository extends JpaRepository<User, Long> {
    boolean existsByCedula(String cedula);
    Optional<User> findByEmail(String email);
    boolean existsByEmail(String email);
    Optional<User> findByTelefono(String telefono); // Opcional, puede conservarse
    Optional<User> findByEmailOrTelefono(String email, String telefono); // Se puede conservar para otros usos
}