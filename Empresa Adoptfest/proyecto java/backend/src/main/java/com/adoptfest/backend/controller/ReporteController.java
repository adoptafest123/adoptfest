package com.adoptfest.backend.controller;

import com.adoptfest.backend.dto.ReporteGeneralResponse;
import com.adoptfest.backend.service.ReporteService;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Va bajo /api/admin/** — SecurityConfig ya exige rol ADMIN
 * automáticamente para todo lo que empiece así.
 */
@RestController
@RequestMapping("/api/admin/reportes")
@RequiredArgsConstructor
public class ReporteController {

    private final ReporteService reporteService;

    @GetMapping("/general")
    public ReporteGeneralResponse reporteGeneral() {
        return reporteService.generarReporteGeneral();
    }
}