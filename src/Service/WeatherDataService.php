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

            $this->entityManager->persist($weatherDataEntity);
        }

        $this->entityManager->flush();
    }
    
}
