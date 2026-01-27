<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KomerceShippingService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('rajaongkir.base_url'), '/');
        $this->apiKey  = config('rajaongkir.api_key');

        if (!$this->apiKey) {
            throw new \Exception('Komerce API key is not set');
        }
    }

    public function shippingRates(array $payload)
    {
        /** @var Response $response */
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->post(
            $this->baseUrl . '/shipping/rates',
            $payload
        );

        if ($response->failed()) {
            throw new \Exception(
                'Komerce API Error: ' . $response->body()
            );
        }

        return $response->json();
    }
}
