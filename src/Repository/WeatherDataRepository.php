<?php

namespace App\Repository;

use App\Entity\WeatherData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WeatherDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherData::class);
    }

    /**
     * Récupère la météo d'une ville à partir d'aujourd'hui.
     */
    public function findWeatherDataFromToday(string $city, \DateTimeInterface $today): array
    {
        $existingData = $this->createQueryBuilder('w')
            ->where('w.city = :city')
            ->andWhere('w.day >= :today')
            ->setParameter('city', $city)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('w.day', 'ASC')
            ->getQuery()
            ->getResult();

        return $existingData;
    }
}
