<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private HttpClientInterface $client;
    private string $UserAgent;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->UserAgent = $_ENV['USERAGENT'];
    }

    public function getCityFromCoords(float $lat, float $lon): ?string
    {
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?lat=%f&lon=%f&format=json&accept-language=fr',
            $lat,
            $lon
        );

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'User-Agent' => $this->UserAgent
            ]
        ]);

        $data = $response->toArray();

        return $data['address']['city'] 
            ?? $data['address']['town'] 
            ?? $data['address']['village'] 
            ?? null;
    }
}
