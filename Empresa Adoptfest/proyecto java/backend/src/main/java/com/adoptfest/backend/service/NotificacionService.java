package com.adoptfest.backend.service;

import com.adoptfest.backend.model.Notificacion;
import com.adoptfest.backend.model.TipoNotificacion;
import com.adoptfest.backend.model.User;
import com.adoptfest.backend.repository.NotificacionRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

@Service
@RequiredArgsConstructor
public class NotificacionService {

    private final NotificacionRepository notificacionRepository;
    // Cuando lleguemos al bloque de Node.js, aquí se agrega un
    // WebSocketNotifierClient que hace un POST interno a Node.js
    // para empujar la notificación en tiempo real.

    public Notificacion crear(User user, String mensaje) {
        return crear(user, "Adoptfest", mensaje, TipoNotificacion.INFO);
    }

    public Notificacion crear(User user, String titulo, String mensaje, TipoNotificacion tipo) {
        Notificacion notificacion = Notificacion.builder()
                .user(user)
                .titulo(titulo)
                .mensaje(mensaje)
                .tipo(tipo)
                .leida(false)
                .build();

        return notificacionRepository.save(notificacion);
    }
}