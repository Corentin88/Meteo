<?php

namespace App\Service;

use App\Entity\WeatherData;
use Doctrine\ORM\EntityManagerInterface;

class WeatherDataService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveWeatherData(string $city, array $dailyData): void
    {
        // Supprimer les anciennes données
        $existingData = $this->entityManager->getRepository(WeatherData::class)
            ->findBy(['city' => $city]);
        foreach ($existingData as $data) {
            $this->entityManager->remove($data);
        }

        // Enregistrer les nouvelles données
        foreach ($dailyData as $dayData) {
            $weatherDataEntity = new WeatherData();

            $date = isset($dayData['day']) ? new \DateTime($dayData['day']) : new \DateTime('now');

            $weatherDataEntity->setCity($city);
            $weatherDataEntity->setDay($date);
            $weatherDataEntity->setWeather($dayData['weather'] ?? '');
            $weatherDataEntity->setIcon($dayData['icon'] ?? '');
            $weatherDataEntity->setSummary($dayData['summary'] ?? '');
            $weatherDataEntity->setTemperature($dayData['temperature'] ?? null);
            $weatherDataEntity->setTemperatureMin($dayData['temperature_min'] ?? null);
            $weatherDataEntity->setTemperatureMax($dayData['temperature_max'] ?? null);
            $weatherDataEntity->setFeelsLike($dayData['feels_like'] ?? null);
            $weatherDataEntity->setWindSpeed($dayData['wind']['speed'] ?? null);
            $weatherDataEntity->setPrecipitationType($dayData['precipitation']['type'] ?? '');
            $weatherDataEntity->setProbabilityPrecipitation($dayData['probability']['precipitation'] ?? null);
            $weatherDataEntity->setProbabilityStorm($dayData['probability']['storm'] ?? null);
            $weatherDataEntity->setProbabilityFreeze($dayData['probability']['freeze'] ?? null);
            $weatherDataEntity->setHumidity($dayData['humidity'] ?? null);
            $weatherDataEntity->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($weatherDataEntity);
        }

        $this->entityManager->flush();
    }
    public function getWeatherDataFromDb(string $city, int $maxAgeMinutes = 60): ?array
    {
        $today = new \DateTime('today');
        $existingData = $this->entityManager
            ->getRepository(WeatherData::class)
            ->findWeatherDataFromToday($city, $today);

        if (empty($existingData)) {
            return null;
        }

        // CORRECTION: Vérifier si la première entrée est trop vieille
        $firstEntry = $existingData[0];
        $updatedAt = $firstEntry->getUpdatedAt();
        
        // Vérification de sécurité pour éviter les erreurs null
        if (!$updatedAt) {
            return null; // Si pas de date de mise à jour, forcer un nouvel appel API
        }
        
        $diffMinutes = (new \DateTime())->getTimestamp() - $updatedAt->getTimestamp();

        if ($diffMinutes > $maxAgeMinutes * 60) {
            return null; // Forcer un nouvel appel API
        }

        // Convertir les entités en tableau pour le contrôleur
        $dailyData = [];
        foreach ($existingData as $data) {
            $dailyData[] = [
                'day' => $data->getDay()->format('Y-m-d'),
                'weather' => $data->getWeather(),
                'icon' => $data->getIcon(),
                'summary' => $data->getSummary(),
                'temperature' => $data->getTemperature(),
                'temperature_min' => $data->getTemperatureMin(),
                'temperature_max' => $data->getTemperatureMax(),
                'feels_like' => $data->getFeelsLike(),
                'wind' => ['speed' => $data->getWindSpeed()],
                'precipitation' => ['type' => $data->getPrecipitationType()],
                'probability' => [
                    'precipitation' => $data->getProbabilityPrecipitation(),
                    'storm' => $data->getProbabilityStorm(),
                    'freeze' => $data->getProbabilityFreeze(),
                ],
                'humidity' => $data->getHumidity(),
            ];
        }

        return $dailyData;
    }}
