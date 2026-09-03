package com.adoptfest.backend.dto;

import com.adoptfest.backend.model.User;

public record AuthResponse(
        String token,
        Long id,
        String nombre,
        String correo,
        String rol,
        String foto
) {
    public static AuthResponse de(User user, String token) {
        return new AuthResponse(token, user.getId(), user.getName(), user.getEmail(), user.getRol().name(), user.getFoto());
    }
}