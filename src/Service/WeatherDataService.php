<?php

namespace App\Service;

use App\Entity\WeatherData;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les opérations liées aux données météorologiques en base de données
 * S'occupe de la persistance et de la récupération des données météo
 */
class WeatherDataService
{
    // Gestionnaire d'entités Doctrine pour les opérations de base de données
    private EntityManagerInterface $entityManager;

    /**
     * Constructeur du service
     * 
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités injecté par Symfony
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Enregistre les nouvelles données météorologiques pour une ville
     * Supprime d'abord les anciennes données pour cette ville
     * 
     * @param string $city Le nom de la ville
     * @param array $dailyData Tableau de données météorologiques quotidiennes
     */
    public function saveWeatherData(string $city, array $dailyData): void
    {
        // Étape 1: Suppression des anciennes données pour cette ville
        $existingData = $this->entityManager->getRepository(WeatherData::class)
            ->findBy(['city' => $city]);
        foreach ($existingData as $data) {
            $this->entityManager->remove($data);
        }

        // Étape 2: Enregistrement des nouvelles données
        foreach ($dailyData as $dayData) {
            $weatherDataEntity = new WeatherData();

            // Gestion de la date avec une valeur par défaut si non fournie
            $date = isset($dayData['day']) ? new \DateTime($dayData['day']) : new \DateTime('now');

            // Mappage des données dans l'entité
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
            
            // Préparation de l'entité pour la persistance
            $this->entityManager->persist($weatherDataEntity);
        }

        // Exécution des opérations en base de données
        $this->entityManager->flush();
    }

    /**
     * Récupère les données météorologiques d'une ville depuis la base de données
     * Vérifie également si les données sont encore fraîches selon l'âge maximum spécifié
     * 
     * @param string $city Le nom de la ville
     * @param int $maxAgeMinutes Âge maximum des données en minutes (60 par défaut)
     * @return array|null Les données météorologiques ou null si trop anciennes ou non trouvées
     */
    public function getWeatherDataFromDb(string $city, int $maxAgeMinutes = 60): ?array
    {
        // Récupération des données météo à partir d'aujourd'hui
        $today = new \DateTime('today');
        $existingData = $this->entityManager
            ->getRepository(WeatherData::class)
            ->findWeatherDataFromToday($city, $today);

        // Si aucune donnée trouvée, retourner null
        if (empty($existingData)) {
            return null;
        }

        // Vérification de la fraîcheur des données
        $firstEntry = $existingData[0];
        $updatedAt = $firstEntry->getUpdatedAt();
        
        // Vérification de sécurité pour éviter les erreurs null
        if (!$updatedAt) {
            return null; // Si pas de date de mise à jour, forcer un nouvel appel API
        }
        
        // Calcul de l'âge des données en secondes
        $diffMinutes = (new \DateTime())->getTimestamp() - $updatedAt->getTimestamp();

        // Vérification si les données sont trop anciennes
        if ($diffMinutes > $maxAgeMinutes * 60) {
            return null; // Forcer un nouvel appel API si les données sont trop anciennes
        }

        // Conversion des entités en tableau pour le contrôleur
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
    }
}
