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
    // TOUJOURS utiliser setParameter, JAMAIS de concaténation
    return $this->createQueryBuilder('w')
        ->where('w.city = :city')
        ->andWhere('w.day >= :today')
        ->setParameter('city', $city)  // Doctrine échappe automatiquement
        ->setParameter('today', $today)
        ->orderBy('w.day', 'ASC')
        ->getQuery()
        ->getResult();
}
}
