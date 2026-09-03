package com.adoptfest.backend.config;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.servlet.config.annotation.ResourceHandlerRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;

import java.nio.file.Path;
import java.nio.file.Paths;

@Configuration
public class WebConfig implements WebMvcConfigurer {

    @Value("${app.upload.dir}")
    private String uploadDir;

    @Override
    public void addResourceHandlers(ResourceHandlerRegistry registry) {
        // Convierte /uploads/archivo.jpg en una URL real accesible desde el navegador
        registry.addResourceHandler("/uploads/**")
                .addResourceLocations(
                        "file:" + resolverDirectorioUploads() + "/",
                        "file:" + resolverDirectorioUploads().getParent().getParent().resolve(uploadDir) + "/"
                );
    }

    private Path resolverDirectorioUploads() {
        try {
            Path clases = Paths.get(WebConfig.class.getProtectionDomain()
                    .getCodeSource().getLocation().toURI());
            return clases.getParent().getParent().resolve(uploadDir).normalize();
        } catch (Exception ignored) {
            return Paths.get(uploadDir).toAbsolutePath().normalize();
        }
    }
}