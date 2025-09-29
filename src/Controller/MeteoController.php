<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Service\WeatherApiService;
use App\Service\WeatherDataService;
use App\Service\GeocodingService;

/**
 * Contrôleur principal pour la gestion des requêtes météorologiques
 * Gère l'affichage des prévisions météo pour une ville donnée
 */
class MeteoController extends AbstractController
{
    // Déclaration des propriétés privées pour les services injectés
    private WeatherApiService $weatherApiService;  // Service pour interagir avec l'API météo
    private WeatherDataService $weatherDataService; // Service pour gérer les données météo en base
    private GeocodingService $geocodingService; // Service pour gérer les données météo en base
    private LoggerInterface $logger;               // Service de journalisation

    /**
     * Constructeur avec injection de dépendances
     *
     * @param WeatherApiService $weatherApiService Service pour les appels à l'API météo
     * @param WeatherDataService $weatherDataService Service pour la gestion des données en base
     * @param LoggerInterface $logger Service de journalisation
     */
    public function __construct(
        WeatherApiService $weatherApiService,
        WeatherDataService $weatherDataService,
        GeocodingService $geocodingService,
        LoggerInterface $logger
    ) {
        $this->weatherApiService = $weatherApiService;
        $this->weatherDataService = $weatherDataService;
        $this->geocodingService = $geocodingService;
        $this->logger = $logger;
    }

    /**
     * Affiche la page d'accueil avec les prévisions météo
     * Route: / (racine du site)
     *
     * @param Request $request Requête HTTP
     * @return Response Réponse HTTP avec le rendu du template
     */
    #[Route('/', name: 'meteo')]
    public function index(Request $request): Response
    {
        // Récupération du paramètre 'ville' depuis l'URL, avec 'Epinal' comme valeur par défaut
        $city = $request->query->get('ville', 'Nancy');
        $lat = $request->query->get('lat', null);
        $lon = $request->query->get('lon', null);
        // Création d'une nouvelle réponse HTTP avec des en-têtes personnalisés
        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'index, follow'); // Configuration SEO

        try {
            // Si lat/lon sont disponibles, priorité sur la météo par coordonnées
            if ($lat !== null && $lon !== null) {
                try {
                    $dailyData = $this->weatherApiService->getWeatherCoord((float)$lat, (float)$lon);
                    $city = $this->geocodingService->getCityFromCoords((float)$lat, (float)$lon) ?? 'Position actuelle';
                    
                    // Extraire le tableau de jours pour correspondre à saveWeatherData()
                    $dailyDataToSave = $dailyData['daily']['data'] ?? $dailyData;
                    
                    // Enregistrer en base uniquement si on a des données
                    if (!empty($dailyDataToSave)) {
                        $this->weatherDataService->saveWeatherData($city, $dailyDataToSave);
                    }
                    // dd($dailyData);
                } catch (\Exception $e) {
                    $this->logger->error('Erreur getWeatherCoord: ' . $e->getMessage());
                    $dailyData = [];
                }
            } else {
                // Sinon, récupération par ville comme avant
                $dailyData = $this->weatherDataService->getWeatherDataFromDb($city, 60);
                if (!$dailyData) {
                    $apiResponse = $this->weatherApiService->getWeatherData($city);
                    $dailyData = $apiResponse['daily']['data'] ?? $apiResponse['daily'] ?? [];
                    if (!empty($dailyData)) {
                        $this->weatherDataService->saveWeatherData($city, $dailyData);
                    }
                }
            }
            // Après avoir récupéré $dailyData
            if (isset($dailyData['daily']['data'])) {
                // Cas API par coordonnées GPS
                $dailyData = $dailyData['daily']['data'];
            } elseif (isset($dailyData['daily'])) {
                // Cas API par ville (selon le provider)
                $dailyData = $dailyData['daily'];
            }

            // Maintenant $dailyData est TOUJOURS une liste de jours
            if (empty($dailyData)) {
                throw new \Exception("Aucune donnée météo disponible");
            }

            // Initialisation du formateur de date en français
            $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);

            // Traitement des données météo du jour
            $todayWeather = $dailyData[0] ?? null;
            if ($todayWeather) {
                $date = new \DateTime($todayWeather['day'] ?? 'now');
                $todayWeather['formattedDay'] = ucfirst($formatter->format($date));
            }

            // Formatage des dates pour chaque jour de prévision
            foreach ($dailyData as &$day) {
                $date = new \DateTime($day['day'] ?? 'now');
                $day['formattedDay'] = ucfirst($formatter->format($date));
            }

            // Rendu du template avec les données formatées
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
                'dailyData' => array_slice($dailyData, 1, 6), // Les 6 prochains jours (sans le jour actuel)
            ], $response);
        } catch (\Exception $e) {
            // En cas d'erreur, on la log et on redirige avec un message d'erreur
            $this->logger->error('Erreur API : ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la récupération des données météo : ' . $e->getMessage());
            return $this->redirectToRoute('meteo');
        }
    }
}
