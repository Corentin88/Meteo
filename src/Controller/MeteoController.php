<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Service\WeatherApiService;
use App\Service\WeatherDataService;

class MeteoController extends AbstractController
{
    private WeatherApiService $weatherApiService;
    private WeatherDataService $weatherDataService;
    private LoggerInterface $logger;

    public function __construct(
        WeatherApiService $weatherApiService,
        WeatherDataService $weatherDataService,
        LoggerInterface $logger
    ) {
        $this->weatherApiService = $weatherApiService;
        $this->weatherDataService = $weatherDataService;
        $this->logger = $logger;
    }

    #[Route('/', name: 'meteo')]
    public function index(Request $request): Response
    {
        $city = $request->query->get('ville', 'Epinal');
        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'index, follow');

        try {
            
            $weatherData = $this->weatherApiService->getWeatherData($city);

            $dailyData = $weatherData['daily']['data'] ?? [];

            $this->weatherDataService->saveWeatherData($city, $dailyData);

            // Formater les dates
            $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);

            $todayWeather = $dailyData[0] ?? null;
            if ($todayWeather) {
                $date = new \DateTime($todayWeather['day'] ?? 'now');
                $todayWeather['formattedDay'] = ucfirst($formatter->format($date));
            }
            foreach ($dailyData as &$day) {
                $date = new \DateTime($day['day'] ?? 'now');
                $day['formattedDay'] = ucfirst($formatter->format($date));
            }

            return $this->render('meteo/index.html.twig', [
                'ville' => $city,
                'day' => $todayWeather['formattedDay'] ?? '',
                'weather' => $todayWeather['weather'] ?? '',
                'icon' => $todayWeather['icon'] ?? '',
                'summary' => $todayWeather['summary'] ?? '',
                'temperature' => $todayWeather['temperature'] ?? null,
                'temperatureMin' => $todayWeather['temperature_min'] ?? null,
                'temperatureMax' => $todayWeather['temperature_max'] ?? null,
                'feelsLike' => $todayWeather['feels_like'] ?? null,
                'windSpeed' => $todayWeather['wind']['speed'] ?? null,
                'precipitationType' => $todayWeather['precipitation']['type'] ?? '',
                'probabilityPrecipitation' => $todayWeather['probability']['precipitation'] ?? null,
                'probabilityStorm' => $todayWeather['probability']['storm'] ?? null,
                'probabilityFreeze' => $todayWeather['probability']['freeze'] ?? null,
                'humidity' => $todayWeather['humidity'] ?? null,
                'dailyData' => array_slice($dailyData, 1, 6),
            ], $response);
        } catch (\Exception $e) {
            $this->logger->error('Erreur API : ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la récupération des données météo : ' . $e->getMessage());
            return $this->redirectToRoute('meteo');
        }
    }
}
