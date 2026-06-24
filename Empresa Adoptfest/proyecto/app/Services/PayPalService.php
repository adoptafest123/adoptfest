<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $mode = config('services.paypal.mode', 'sandbox');

        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->clientId     = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
    }

    protected function obtenerToken(): ?string
    {
        $respuesta = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($respuesta->failed()) {
            Log::error('PayPal: fallo al obtener token', $respuesta->json() ?? []);
            return null;
        }

        return $respuesta->json('access_token');
    }

    public function crearOrden(float $monto, string $moneda = 'USD'): ?array
    {
        $token = $this->obtenerToken();
        if (!$token) return null;

        $respuesta = Http::withToken($token)
            ->asJson()
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $moneda,
                        'value'         => number_format($monto, 2, '.', ''),
                    ],
                    'description' => 'Donación a Adoptafest',
                ]],
                'application_context' => [
                    'brand_name' => 'Adoptafest',
                    'return_url' => route('donaciones.paypal.exito'),
                    'cancel_url' => route('donaciones.paypal.cancelado'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($respuesta->failed()) {
            Log::error('PayPal: fallo al crear orden', $respuesta->json() ?? []);
            return null;
        }

        return $respuesta->json();
    }


    public function capturarOrden(string $orderId): ?array
    {
        $token = $this->obtenerToken();
        if (!$token) return null;

        $respuesta = Http::withToken($token)
            ->asJson()
            ->withBody('{}', 'application/json')
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if ($respuesta->failed()) {
            Log::error('PayPal: fallo al capturar orden', $respuesta->json() ?? []);
            return null;
        }

        return $respuesta->json();
    }
}