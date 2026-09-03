package com.adoptfest.backend.service;

import com.adoptfest.backend.dto.AdminUserRequest;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;

@Service
@RequiredArgsConstructor
public class AdminUserService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;

    public List<User> listar(String buscar) {
        if (buscar == null || buscar.isBlank()) {
            return userRepository.findAll();
        }
        String q = buscar.toLowerCase();
        return userRepository.findAll().stream()
                .filter(u -> u.getName().toLowerCase().contains(q) || u.getEmail().toLowerCase().contains(q))
                .toList();
    }

    public User crear(AdminUserRequest datos) {
        if (userRepository.existsByEmail(datos.correo())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Ese correo ya está registrado.");
        }
        if (datos.contrasena() == null || datos.contrasena().isBlank()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "La contraseña es obligatoria al crear un usuario.");
        }

        User user = User.builder()
                .name(datos.nombre())
                .email(datos.correo())
                .cedula(datos.cedula())
                .telefono(datos.telefono())
                .password(passwordEncoder.encode(datos.contrasena()))
                .rol(datos.rol())
                .puntosDonante(0)
                .build();

        return userRepository.save(user);
    }

    public User actualizar(Long id, AdminUserRequest datos, Long idAdminActual) {
    User user = obtenerOFallar(id);

    // Nadie puede cambiar su propio rol, ni siquiera un admin —
    // evita quedarte fuera del panel por error, como ya te pasó.
    if (id.equals(idAdminActual) && datos.rol() != user.getRol()) {
        throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "No puedes cambiar tu propio rol.");
    }

    user.setName(datos.nombre());
    user.setCedula(datos.cedula());
    user.setTelefono(datos.telefono());
    user.setRol(datos.rol());

    if (datos.contrasena() != null && !datos.contrasena().isBlank()) {
        user.setPassword(passwordEncoder.encode(datos.contrasena()));
    }

    return userRepository.save(user);
}

    public void eliminar(Long id, Long idAdminActual) {
        if (id.equals(idAdminActual)) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "No puedes eliminar tu propia cuenta.");
        }
        userRepository.delete(obtenerOFallar(id));
    }

    private User obtenerOFallar(Long id) {
        return userRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Usuario no encontrado."));
    }
}