// src/main/java/com/adoptfest/backend/service/AuthService.java
package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.AuthResponse;
import com.adoptfest.backend.dto.LoginRequest;
import com.adoptfest.backend.dto.RegistroRequest;
import com.adoptfest.backend.model.Rol;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.repository.UserRepository;
import com.adoptfest.backend.security.JwtUtil;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.util.regex.Pattern;

@Service
@RequiredArgsConstructor
public class AuthService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtUtil jwtUtil;
    
    // Patrón para validar formato de correo electrónico
    private static final Pattern EMAIL_PATTERN = 
            Pattern.compile("^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$");

    public AuthResponse registrar(RegistroRequest req) {
        // Validar formato de correo
        if (!esEmailValido(req.correo())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Ingresa un correo electrónico válido.");
        }

        if (userRepository.existsByEmail(req.correo())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Ese correo ya está registrado.");
        }
        if (userRepository.existsByCedula(req.cedula())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Ya existe una cuenta registrada con esa cédula.");
        }

        User user = User.builder()
                .name(req.nombre())
                .email(req.correo())
                .cedula(req.cedula())
                .telefono(req.telefono())
                .password(passwordEncoder.encode(req.contrasena()))
                .rol(Rol.CLIENTE)
                .puntosDonante(0)
                .build();

        User guardado = userRepository.save(user);
        String token = jwtUtil.generarToken(guardado);
        return AuthResponse.de(guardado, token);
    }

    public AuthResponse login(LoginRequest req) {
        String email = req.identificador().trim();

        // 1. Validar que no esté vacío
        if (email == null || email.isBlank()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Ingresa tu correo electrónico.");
        }

        // 2. Validar formato de correo
        if (!esEmailValido(email)) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Ingresa un correo electrónico válido (ej: usuario@dominio.com).");
        }

        // 3. Buscar usuario por email (SOLO por email, no por teléfono)
        User usuario = userRepository.findByEmail(email)
                .orElseThrow(() -> new ResponseStatusException(
                        HttpStatus.UNAUTHORIZED, 
                        "No existe una cuenta con ese correo electrónico."
                ));

        // 4. Verificar contraseña
        if (!passwordEncoder.matches(req.contrasena(), usuario.getPassword())) {
            throw new ResponseStatusException(
                    HttpStatus.UNAUTHORIZED, 
                    "Contraseña incorrecta. Por favor, verifica tus credenciales."
            );
        }

        // 5. Generar token
        String token = jwtUtil.generarToken(usuario);
        return AuthResponse.de(usuario, token);
    }

    private boolean esEmailValido(String email) {
        if (email == null || email.isBlank()) {
            return false;
        }
        return EMAIL_PATTERN.matcher(email).matches();
    }
}