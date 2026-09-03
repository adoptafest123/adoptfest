package com.adoptfest.backend.model;

public enum CategoriaDonacion {
    ALIMENTO(50), HIGIENE(30), JUGUETES(15), COBIJAS_CAMAS(40), MEDICAMENTOS(60), OTROS(10);

    private final int puntosPorUnidad;

    CategoriaDonacion(int puntosPorUnidad) {
        this.puntosPorUnidad = puntosPorUnidad;
    }

    public int getPuntosPorUnidad() {
        return puntosPorUnidad;
    }
}