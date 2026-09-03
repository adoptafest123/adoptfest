 package com.adoptfest.backend.controller;
import com.adoptfest.backend.model.EstadoSolicitud;
import com.adoptfest.backend.repository.SolicitudAdopcionRepository;
import java.util.Map;
import com.adoptfest.backend.dto.MascotaRequest;
import com.adoptfest.backend.model.Mascota;
import com.adoptfest.backend.repository.MascotaRepository;
import com.adoptfest.backend.service.FileStorageService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;
import java.util.List;
import java.util.Random;

@RestController
@RequiredArgsConstructor
public class MascotaController {

    private final MascotaRepository mascotaRepository;
    private final FileStorageService fileStorageService;
    private final SolicitudAdopcionRepository solicitudAdopcionRepository;

    @GetMapping("/api/mascotas")
    public List<Mascota> listarPublico() {
        return mascotaRepository.findAll();
    }

    @GetMapping("/api/mascotas/{id}")
    public Mascota obtener(@PathVariable Long id) {
        return mascotaRepository.findById(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Mascota no encontrada."));
    }

    @GetMapping("/api/mascotas/{id}/interes")
    public Map<String, Long> interes(@PathVariable Long id) {
        long pendientes = solicitudAdopcionRepository
                .countByMascotaIdAndEstado(id, EstadoSolicitud.PENDIENTE);
        return Map.of("solicitudesPendientes", pendientes);
    }

    @GetMapping("/api/admin/mascotas")
    public List<Mascota> listarAdmin() {
        return mascotaRepository.findAll();
    }

@PostMapping("/api/admin/mascotas")
public ResponseEntity<Mascota> crear(@Valid @RequestBody MascotaRequest datos) {
    Mascota mascota = Mascota.builder()
            .codigo(generarCodigo())
            .nombre(datos.nombre())
            .tipo(datos.tipo())
            .edad(datos.edad())
            .raza(datos.raza())
            .peso(datos.peso())
            .estatura(datos.estatura())
            .descripcion(datos.descripcion())
            .imagen(datos.imagen())
            .estado(datos.estado())
            .vacunado(datos.vacunado())
            .esterilizado(datos.esterilizado())
            .build();

    return ResponseEntity.ok(mascotaRepository.save(mascota));
}


@PutMapping("/api/admin/mascotas/{id}")
public ResponseEntity<Mascota> actualizar(@PathVariable Long id, @Valid @RequestBody MascotaRequest datos) {
    Mascota mascota = mascotaRepository.findById(id)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Mascota no encontrada."));

    mascota.setNombre(datos.nombre());
    mascota.setTipo(datos.tipo());
    mascota.setEdad(datos.edad());
    mascota.setRaza(datos.raza());
    mascota.setPeso(datos.peso());
    mascota.setEstatura(datos.estatura());
    mascota.setDescripcion(datos.descripcion());
    mascota.setEstado(datos.estado());
    mascota.setVacunado(datos.vacunado());
    mascota.setEsterilizado(datos.esterilizado());

    if (datos.imagen() != null && !datos.imagen().equals(mascota.getImagen())) {
        fileStorageService.eliminar(mascota.getImagen());
        mascota.setImagen(datos.imagen());
    }
  return ResponseEntity.ok(mascotaRepository.save(mascota));
}

    @DeleteMapping("/api/admin/mascotas/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Long id) {
        if (!mascotaRepository.existsById(id)) {
            throw new ResponseStatusException(HttpStatus.NOT_FOUND, "Mascota no encontrada.");
        }
        mascotaRepository.deleteById(id);
        return ResponseEntity.noContent().build();
    }

    private String generarCodigo() {
        String letras = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        StringBuilder sb = new StringBuilder();
        Random r = new Random();
        String codigo;
        do {
            sb.setLength(0);
            for (int i = 0; i < 3; i++) sb.append(letras.charAt(r.nextInt(letras.length())));
            codigo = sb + "-" + (100 + r.nextInt(900));
        } while (mascotaRepository.existsByCodigo(codigo));
        return codigo;
    }
}