package com.adoptfest.backend.service;

import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.*;
import org.springframework.stereotype.Service;
import org.springframework.util.LinkedMultiValueMap;
import org.springframework.util.MultiValueMap;
import org.springframework.web.client.HttpClientErrorException;
import org.springframework.web.client.RestTemplate;

import java.math.BigDecimal;
import java.util.List;
import java.util.Map;

@Slf4j
@Service
public class PayPalService {

    private final RestTemplate restTemplate = new RestTemplate();

    @Value("${app.paypal.client-id}")
    private String clientId;

    @Value("${app.paypal.client-secret}")
    private String clientSecret;

    @Value("${app.paypal.mode}")
    private String modo;

    @Value("${app.frontend.url}")
    private String frontendUrl;

    private String baseUrl() {
        return "live".equals(modo) ? "https://api-m.paypal.com" : "https://api-m.sandbox.paypal.com";
    }

    private String obtenerToken() {
        try {
            log.info("🔐 Obteniendo token de PayPal...");
            log.info("🌐 URL: {}", baseUrl() + "/v1/oauth2/token");
            log.info("👤 Client ID: {}", clientId.substring(0, Math.min(10, clientId.length())) + "...");

            HttpHeaders headers = new HttpHeaders();
            headers.setContentType(MediaType.APPLICATION_FORM_URLENCODED);
            headers.setBasicAuth(clientId, clientSecret);

            MultiValueMap<String, String> body = new LinkedMultiValueMap<>();
            body.add("grant_type", "client_credentials");

            HttpEntity<MultiValueMap<String, String>> request = new HttpEntity<>(body, headers);

            ResponseEntity<Map> respuesta = restTemplate.postForEntity(
                    baseUrl() + "/v1/oauth2/token", request, Map.class
            );

            log.info("✅ Respuesta de PayPal: Status {}", respuesta.getStatusCode());

            if (respuesta.getStatusCode() == HttpStatus.OK) {
                String token = (String) respuesta.getBody().get("access_token");
                log.info("✅ Token obtenido exitosamente");
                return token;
            } else {
                log.error("❌ Error al obtener token. Status: {}, Body: {}", 
                    respuesta.getStatusCode(), respuesta.getBody());
                throw new RuntimeException("Error al obtener token de PayPal: " + respuesta.getStatusCode());
            }
        } catch (HttpClientErrorException e) {
            log.error("❌ Error HTTP al obtener token de PayPal: {}", e.getMessage());
            log.error("📋 Body: {}", e.getResponseBodyAsString());
            
            if (e.getStatusCode() == HttpStatus.UNAUTHORIZED) {
                throw new RuntimeException(
                    "Credenciales de PayPal inválidas. Verifica tu Client ID y Secret en application.properties. " +
                    "Error: " + e.getResponseBodyAsString()
                );
            }
            throw new RuntimeException("Error al conectar con PayPal: " + e.getMessage());
        } catch (Exception e) {
            log.error("❌ Error inesperado al obtener token de PayPal: ", e);
            throw new RuntimeException("Error inesperado al conectar con PayPal: " + e.getMessage());
        }
    }

    @SuppressWarnings("unchecked")
    public Map<String, Object> crearOrden(BigDecimal monto, String moneda) {
        try {
            log.info("💰 Creando orden de PayPal para: {} {}", monto, moneda);
            
            String token = obtenerToken();
            log.info("🔑 Token obtenido correctamente");

            HttpHeaders headers = new HttpHeaders();
            headers.setBearerAuth(token);
            headers.setContentType(MediaType.APPLICATION_JSON);

            Map<String, Object> orden = Map.of(
                    "intent", "CAPTURE",
                    "purchase_units", List.of(Map.of(
                            "amount", Map.of(
                                    "currency_code", moneda,
                                    "value", monto.setScale(2, java.math.RoundingMode.HALF_UP).toString()
                            ),
                            "description", "Donación a Adoptfest"
                    )),
                    "application_context", Map.of(
                            "brand_name", "Adoptfest",
                            "return_url", frontendUrl + "/donaciones/paypal/exito",
                            "cancel_url", frontendUrl + "/donaciones/paypal/cancelado",
                            "user_action", "PAY_NOW"
                    )
            );

            HttpEntity<Map<String, Object>> request = new HttpEntity<>(orden, headers);
            ResponseEntity<Map> respuesta = restTemplate.postForEntity(
                    baseUrl() + "/v2/checkout/orders", request, Map.class
            );

            log.info("✅ Orden creada exitosamente. Status: {}", respuesta.getStatusCode());
            return respuesta.getBody();

        } catch (HttpClientErrorException e) {
            log.error("❌ Error HTTP al crear orden PayPal: {}", e.getMessage());
            log.error("📋 Body: {}", e.getResponseBodyAsString());
            throw new RuntimeException("Error al crear la orden de PayPal: " + e.getResponseBodyAsString());
        } catch (Exception e) {
            log.error("❌ Error inesperado al crear orden PayPal: ", e);
            throw new RuntimeException("Error al crear la orden de PayPal: " + e.getMessage());
        }
    }

    @SuppressWarnings("unchecked")
    public Map<String, Object> capturarOrden(String orderId) {
        try {
            log.info("📦 Capturando orden PayPal: {}", orderId);
            
            String token = obtenerToken();

            HttpHeaders headers = new HttpHeaders();
            headers.setBearerAuth(token);
            headers.setContentType(MediaType.APPLICATION_JSON);

            HttpEntity<String> request = new HttpEntity<>("{}", headers);
            ResponseEntity<Map> respuesta = restTemplate.postForEntity(
                    baseUrl() + "/v2/checkout/orders/" + orderId + "/capture", request, Map.class
            );

            log.info("✅ Orden capturada. Status: {}", respuesta.getStatusCode());
            return respuesta.getBody();

        } catch (HttpClientErrorException e) {
            log.error("❌ Error HTTP al capturar orden PayPal: {}", e.getMessage());
            log.error("📋 Body: {}", e.getResponseBodyAsString());
            throw new RuntimeException("Error al capturar la orden de PayPal: " + e.getResponseBodyAsString());
        } catch (Exception e) {
            log.error("❌ Error inesperado al capturar orden PayPal: ", e);
            throw new RuntimeException("Error al capturar la orden de PayPal: " + e.getMessage());
        }
    }

    @SuppressWarnings("unchecked")
    public String extraerLinkAprobacion(Map<String, Object> orden) {
        try {
            List<Map<String, String>> links = (List<Map<String, String>>) orden.get("links");
            String link = links.stream()
                    .filter(l -> "approve".equals(l.get("rel")))
                    .map(l -> l.get("href"))
                    .findFirst()
                    .orElse(null);
            
            log.info("🔗 Link de aprobación extraído: {}", link);
            return link;
        } catch (Exception e) {
            log.error("❌ Error al extraer link de aprobación: ", e);
            return null;
        }
    }
}