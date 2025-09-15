<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WeatherApiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['RAPIDAPI_KEY'];
        $this->cache = $cache;
    }

    public function getWeatherData(string $city): array
    {
        $city = trim($city);
        $city = htmlspecialchars($city, ENT_QUOTES, 'UTF-8');
        $city = mb_substr($city, 0, 50);

        $cacheKey = 'weather_data_' . strtolower($city);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
            $item->expiresAfter(43200);

            // Obtenir l'ID de l'emplacement
            $locationUrl = 'https://ai-weather-by-meteosource.p.rapidapi.com/find_places?text=' . urlencode($city) . '&language=fr';
            $locationResponse = $this->client->request('GET', $locationUrl, [
                'headers' => [
                    'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey,
                ]
            ]);

            $locationData = json_decode($locationResponse->getContent(), true);
            if (empty($locationData)) {
                throw new \Exception('Aucun emplacement trouvé pour cette ville');
            }

            $placeId = $locationData[0]['place_id'];

            // Obtenir les données météo
            $url = 'https://ai-weather-by-meteosource.p.rapidapi.com/daily?place_id=' . $placeId . '&language=fr&units=metric';
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey,
                ]
            ]);

            return $response->toArray();
        });
    }
}
