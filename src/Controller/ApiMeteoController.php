<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\WeatherData;
use Symfony\Component\HttpFoundation\Response;


class ApiMeteoController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/meteo/{ville}', name: 'api_meteo', methods: ['GET'])]
    public function getWeatherData(string $ville): Response
    {
        $weatherData = $this->entityManager->getRepository(WeatherData::class)
            ->findBy(['city' => $ville], ['day' => 'ASC']);
    
        $response = [];
        foreach ($weatherData as $data) {
            $response[] = [
                'city' => $data->getCity(),
                'day' => $data->getDay(),
                'weather' => $data->getWeather(),
                'icon' => $data->getIcon(),
                'summary' => $data->getSummary(),
                'temperature' => $data->getTemperature(),
                'temperature_min' => $data->getTemperatureMin(),
                'temperature_max' => $data->getTemperatureMax(),
                'feels_like' => $data->getFeelsLike(),
                'wind_speed' => $data->getWindSpeed(),
                'precipitation_type' => $data->getPrecipitationType(),
                'probability_precipitation' => $data->getProbabilityPrecipitation(),
                'probability_storm' => $data->getProbabilityStorm(),
                'probability_freeze' => $data->getProbabilityFreeze(),
                'humidity' => $data->getHumidity()
            ];
        }
    
        return new Response(json_encode($response), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}