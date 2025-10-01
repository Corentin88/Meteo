<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class WeatherRetrievalService
{
    public function __construct(
        private WeatherApiService $weatherApiService,
        private WeatherDataService $weatherDataService,
        private GeocodingService $geocodingService,
        private LoggerInterface $logger
    ) {}

    /**
     * Récupère les données météo par coordonnées GPS
     */
    public function getWeatherByCoordinates(float $lat, float $lon): array
    {
        try {
            $dailyData = $this->weatherApiService->getWeatherCoord($lat, $lon);
            $city = $this->geocodingService->getCityFromCoords($lat, $lon) ?? 'Position actuelle';
            
            $dailyDataToSave = $dailyData['daily']['data'] ?? $dailyData;
            
            if (!empty($dailyDataToSave)) {
                $this->weatherDataService->saveWeatherData($city, $dailyDataToSave);
            }
            
            return ['data' => $dailyData, 'city' => $city];
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur getWeatherCoord: ' . $e->getMessage());
            throw new \RuntimeException('Impossible de récupérer la météo pour ces coordonnées');
        }
    }

    /**
     * Récupère les données météo par ville
     */
    public function getWeatherByCity(string $city): array
    {
        // Essai depuis la base de données (cache 60 min)
        $dailyData = $this->weatherDataService->getWeatherDataFromDb($city, 60);
        
        if (!$dailyData) {
            // Si pas en cache, appel à l'API
            $apiResponse = $this->weatherApiService->getWeatherData($city);
            $dailyData = $apiResponse['daily']['data'] ?? $apiResponse['daily'] ?? [];
            
            if (empty($dailyData)) {
                throw new \RuntimeException("Aucune donnée météo disponible pour {$city}");
            }
            
            $this->weatherDataService->saveWeatherData($city, $dailyData);
        }
        
        return ['data' => $dailyData, 'city' => $city];
    }
}