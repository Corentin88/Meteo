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

    #[Route('/', name: 'meteo')]
    public function index(Request $request): Response
    {
        try {
            // Récupération et nettoyage de la ville
            $ville = trim($request->query->get('ville', 'Epinal'));
            // Nettoie la ville : supprime les balises HTML et échappe les caractères spéciaux
            $ville = htmlspecialchars($ville, ENT_QUOTES, 'UTF-8');
            // Validation : uniquement des lettres, espaces, tirets et apostrophes
            if (!preg_match('/^[\p{L}\s\'-]+$/u', $ville)) {
                $this->addFlash('error', 'Le nom de la ville contient des caractères non autorisés.Retour sur Epinal ');
                $ville = 'Epinal'; // Valeur par défaut en cas d'erreur
            }
            
            // Limiter la longueur du nom de ville
            $ville = mb_substr($ville, 0, 50);
            
            $cacheKey = 'weather_data_' . strtolower($ville);

            $weatherData = $this->cache->get($cacheKey, function (ItemInterface $item) use ($ville) {
                $item->expiresAfter(43200);
                
                // D'abord, obtenir l'ID de l'emplacement
                $locationUrl = 'https://ai-weather-by-meteosource.p.rapidapi.com/find_places?text=' . urlencode($ville) . '&language=fr';
                
                $locationResponse = $this->client->request('GET', $locationUrl, [
                    'headers' => [
                        'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                        'x-rapidapi-key' => $this->apiKey,
                    ]
                ]);
                
                $locationData = json_decode($locationResponse->getContent(), true);
                
                if (empty($locationData)) {
                    throw new \Exception('Aucun emplacement trouvé pour cette ville');
                }
                
                // Prendre le premier résultat
                $placeId = $locationData[0]['place_id'];
                
                // Maintenant, obtenir les données météo avec le bon place_id
                $url = 'https://ai-weather-by-meteosource.p.rapidapi.com/daily?place_id=' . $placeId . '&language=fr&units=metric';

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
// Toujours avoir une date
                $date = $dayData['day'] instanceof \DateTimeInterface ? $dayData['day'] : new \DateTime('now');

                $weatherDataEntity->setCity($ville);
                $weatherDataEntity->setDay($date);
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
            $this->addFlash('error', 'Erreur lors de la récupération des données météo : ' . $e->getMessage());
            return $this->redirectToRoute('meteo');
        }
    }
}
