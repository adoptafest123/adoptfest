// src/main/java/com/adoptfest/backend/service/RefugioService.java
package com.adoptfest.backend.service;

import com.adoptfest.backend.model.DonacionDinero;
import com.adoptfest.backend.model.DonacionEspecie;
import com.adoptfest.backend.model.EstadoDonacionDinero;
import com.adoptfest.backend.model.EstadoDonacionEspecie;
import com.adoptfest.backend.model.Refugio;
import com.adoptfest.backend.repository.DonacionDineroRepository;
import com.adoptfest.backend.repository.DonacionEspecieRepository;
import com.adoptfest.backend.repository.RefugioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Service
@RequiredArgsConstructor
public class RefugioService {

    private final RefugioRepository refugioRepository;
    private final DonacionDineroRepository donacionDineroRepository;
    private final DonacionEspecieRepository donacionEspecieRepository;

    public List<Refugio> listarActivos() {
        return refugioRepository.findByActivoTrueOrderByNombreAsc();
    }

    public List<Refugio> listarTodos() {
        return refugioRepository.findAll();
    }

    public Refugio obtener(Long id) {
        return refugioRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Refugio no encontrado"));
    }

    public Refugio crear(Refugio refugio) {
        return refugioRepository.save(refugio);
    }

    public Refugio actualizar(Long id, Refugio refugioActualizado) {
        Refugio refugio = obtener(id);
        refugio.setNombre(refugioActualizado.getNombre());
        refugio.setDireccion(refugioActualizado.getDireccion());
        refugio.setTelefono(refugioActualizado.getTelefono());
        refugio.setEmail(refugioActualizado.getEmail());
        refugio.setDescripcion(refugioActualizado.getDescripcion());
        refugio.setImagen(refugioActualizado.getImagen());
        refugio.setActivo(refugioActualizado.getActivo());
        return refugioRepository.save(refugio);
    }

    public void eliminar(Long id) {
        refugioRepository.deleteById(id);
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTES DE REFUGIOS
    // ═══════════════════════════════════════════════════════════

    public Map<String, Object> obtenerEstadisticasRefugio(Long refugioId) {
        Refugio refugio = obtener(refugioId);
        
        // Donaciones en dinero del refugio
        List<DonacionDinero> donacionesDinero = donacionDineroRepository.findByRefugioId(refugioId);
        
        // Donaciones en especie del refugio
        List<DonacionEspecie> donacionesEspecie = donacionEspecieRepository.findByRefugioId(refugioId);
        
        // Calcular total recaudado en dinero
        BigDecimal totalDinero = donacionesDinero.stream()
                .filter(d -> d.getEstado() == EstadoDonacionDinero.COMPLETADO)
                .map(DonacionDinero::getMonto)
                .reduce(BigDecimal.ZERO, BigDecimal::add);
        
        // Contar donaciones en especie por categoría
        Map<String, Long> donacionesPorCategoria = new HashMap<>();
        for (DonacionEspecie d : donacionesEspecie) {
            if (d.getEstado() == EstadoDonacionEspecie.CONFIRMADO || d.getEstado() == EstadoDonacionEspecie.APROBADO) {
                String key = d.getCategoria().name();
                donacionesPorCategoria.put(key, donacionesPorCategoria.getOrDefault(key, 0L) + 1);
            }
        }
        
        // Total de donaciones en especie
        long totalEspecie = donacionesEspecie.stream()
                .filter(d -> d.getEstado() == EstadoDonacionEspecie.CONFIRMADO || d.getEstado() == EstadoDonacionEspecie.APROBADO)
                .count();
        
        Map<String, Object> resultado = new HashMap<>();
        resultado.put("refugio", refugio);
        resultado.put("totalDonacionesDinero", donacionesDinero.size());
        resultado.put("totalRecaudadoDinero", totalDinero);
        resultado.put("totalDonacionesEspecie", totalEspecie);
        resultado.put("donacionesPorCategoria", donacionesPorCategoria);
        resultado.put("donacionesDinero", donacionesDinero);
        resultado.put("donacionesEspecie", donacionesEspecie);
        
        return resultado;
    }

    public Map<String, Object> obtenerEstadisticasTodosRefugios() {
        List<Refugio> refugios = listarTodos();
        Map<String, Object> resultado = new HashMap<>();
        List<Map<String, Object>> stats = new ArrayList<>();
        
        for (Refugio refugio : refugios) {
            Map<String, Object> stat = new HashMap<>();
            
            // Donaciones en dinero
            List<DonacionDinero> donacionesDinero = donacionDineroRepository.findByRefugioId(refugio.getId());
            BigDecimal totalDinero = donacionesDinero.stream()
                    .filter(d -> d.getEstado() == EstadoDonacionDinero.COMPLETADO)
                    .map(DonacionDinero::getMonto)
                    .reduce(BigDecimal.ZERO, BigDecimal::add);
            
            // Donaciones en especie
            List<DonacionEspecie> donacionesEspecie = donacionEspecieRepository.findByRefugioId(refugio.getId());
            long totalEspecie = donacionesEspecie.stream()
                    .filter(d -> d.getEstado() == EstadoDonacionEspecie.CONFIRMADO || d.getEstado() == EstadoDonacionEspecie.APROBADO)
                    .count();
            
            stat.put("id", refugio.getId());
            stat.put("nombre", refugio.getNombre());
            stat.put("direccion", refugio.getDireccion());
            stat.put("telefono", refugio.getTelefono());
            stat.put("email", refugio.getEmail());
            stat.put("activo", refugio.getActivo());
            stat.put("totalDonacionesDinero", donacionesDinero.size());
            stat.put("totalRecaudadoDinero", totalDinero);
            stat.put("totalDonacionesEspecie", totalEspecie);
            
            stats.add(stat);
        }
        
        resultado.put("refugios", stats);
        return resultado;
    }
}