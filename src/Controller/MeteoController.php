<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\WeatherData;

class MeteoController extends AbstractController
{
    private HttpClientInterface $client;
    private string $apiKey;
    private LoggerInterface $logger;
    private CacheInterface $cache;
    private EntityManagerInterface $entityManager;

    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger,
        CacheInterface $cache,
        EntityManagerInterface $entityManager
    ) {
        $this->client = $client;
        $this->apiKey = $_ENV['RAPIDAPI_KEY'];
        $this->logger = $logger;
        $this->cache = $cache;
        $this->entityManager = $entityManager;
    }

    #[Route('/meteo', name: 'meteo')]
    public function index(Request $request): Response
    {
        try {
            $ville = $request->query->get('ville', 'Xertigny');
            $cacheKey = 'weather_data_' . strtolower($ville);

            $weatherData = $this->cache->get($cacheKey, function (ItemInterface $item) use ($ville) {
                $item->expiresAfter(43200);
                $url = 'https://ai-weather-by-meteosource.p.rapidapi.com/daily?place_id=' . urlencode($ville);

                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                        'x-rapidapi-key' => $this->apiKey,
                    ]
                ]);

                return $response->toArray();
            });

            $dailyData = $weatherData['daily']['data'] ?? [];

            // Créer le formatteur de date une seule fois
            $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);

            // Supprimer toutes les données existantes pour cette ville
            $existingData = $this->entityManager->getRepository(WeatherData::class)
                ->findBy(['city' => $ville]);

            foreach ($existingData as $data) {
                $this->entityManager->remove($data);
            }

            // Créer une entité pour chaque jour
            foreach ($dailyData as $dayData) {
                $weatherDataEntity = new WeatherData();

                $date = new \DateTime($dayData['day'] ?? 'N/A');
                $formattedDay = ucfirst($formatter->format($date));

                $weatherDataEntity->setCity($ville);
                $weatherDataEntity->setDay($formattedDay);
                $weatherDataEntity->setWeather($dayData['weather']);
                $weatherDataEntity->setIcon($dayData['icon']);
                $weatherDataEntity->setSummary($dayData['summary']);
                $weatherDataEntity->setTemperature($dayData['temperature']);
                $weatherDataEntity->setTemperatureMin($dayData['temperature_min']);
                $weatherDataEntity->setTemperatureMax($dayData['temperature_max']);
                $weatherDataEntity->setFeelsLike($dayData['feels_like']);
                $weatherDataEntity->setWindSpeed($dayData['wind']['speed']);
                $weatherDataEntity->setPrecipitationType($dayData['precipitation']['type']);
                $weatherDataEntity->setProbabilityPrecipitation($dayData['probability']['precipitation']);
                $weatherDataEntity->setProbabilityStorm($dayData['probability']['storm']);
                $weatherDataEntity->setProbabilityFreeze($dayData['probability']['freeze']);
                $weatherDataEntity->setHumidity($dayData['humidity']);

                $this->entityManager->persist($weatherDataEntity);
            }

            $this->entityManager->flush();

            // Récupérer les données pour le premier jour pour l'affichage
            $todayWeather = $dailyData[0] ?? null;

            // Formater la date pour le premier jour
            if ($todayWeather) {
                $date = new \DateTime($todayWeather['day'] ?? 'N/A');
                $todayWeather['formattedDay'] = ucfirst($formatter->format($date));
            }

            // Formater les dates pour les autres jours
            foreach ($dailyData as &$day) {
                $date = new \DateTime($day['day'] ?? 'N/A');
                $day['formattedDay'] = ucfirst($formatter->format($date));
            }

            return $this->render('meteo/index.html.twig', [
                'ville' => $ville,
                'day' => $todayWeather ? $todayWeather['formattedDay'] : '',
                'weather' => $todayWeather ? $todayWeather['weather'] : '',
                'icon' => $todayWeather ? $todayWeather['icon'] : '',
                'summary' => $todayWeather ? $todayWeather['summary'] : '',
                'temperature' => $todayWeather ? $todayWeather['temperature'] : null,
                'temperatureMin' => $todayWeather ? $todayWeather['temperature_min'] : null,
                'temperatureMax' => $todayWeather ? $todayWeather['temperature_max'] : null,
                'feelsLike' => $todayWeather ? $todayWeather['feels_like'] : null,
                'windSpeed' => $todayWeather ? $todayWeather['wind']['speed'] : null,
                'precipitationType' => $todayWeather ? $todayWeather['precipitation']['type'] : '',
                'probabilityPrecipitation' => $todayWeather ? $todayWeather['probability']['precipitation'] : null,
                'probabilityStorm' => $todayWeather ? $todayWeather['probability']['storm'] : null,
                'probabilityFreeze' => $todayWeather ? $todayWeather['probability']['freeze'] : null,
                'humidity' => $todayWeather ? $todayWeather['humidity'] : null,
                'dailyData' => array_slice($dailyData, 1, 6)
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur API : ' . $e->getMessage());
            return new Response('Erreur lors de la récupération des données météo : ' . $e->getMessage(), 500);
        }
    }
}
