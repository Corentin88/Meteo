<?php

namespace App\Repository;

use App\Entity\WeatherData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité WeatherData
 * Fournit des méthodes pour récupérer des données météorologiques depuis la base de données
 */
class WeatherDataRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du repository
     * Initialise le repository avec le gestionnaire d'entités et la classe d'entité
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités
     */
    public function __construct(ManagerRegistry $registry)
    {
        // Appel au constructeur parent avec l'entité WeatherData
        parent::__construct($registry, WeatherData::class);
    }

    /**
     * Récupère les données météorologiques d'une ville à partir d'une date donnée
     * 
     * @param string $city Le nom de la ville pour laquelle récupérer les données
     * @param \DateTimeInterface $today La date à partir de laquelle récupérer les données (inclusive)
     * @return WeatherData[] Un tableau d'objets WeatherData triés par date croissante
     */
    public function findWeatherDataFromToday(string $city, \DateTimeInterface $today): array
    {
        // Création d'un constructeur de requête pour l'entité WeatherData (alias 'w')
        $existingData = $this->createQueryBuilder('w')
            // Filtre sur le nom de la ville (correspondance exacte)
            ->where('w.city = :city')
            // Filtre pour ne récupérer que les données à partir d'aujourd'hui
            ->andWhere('w.day >= :today')
            // Définition des paramètres de la requête
            ->setParameter('city', $city)
            ->setParameter('today', $today->format('Y-m-d'))
            // Tri par date croissante
            ->orderBy('w.day', 'ASC')
            // Exécution de la requête et récupération des résultats
            ->getQuery()
            ->getResult();

        return $existingData;
    }
}
