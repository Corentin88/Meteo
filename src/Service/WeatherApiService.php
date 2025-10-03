<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service pour interagir avec l'API météo externe
 * Gère la récupération des données météorologiques et la mise en cache
 */
class WeatherApiService
{
    // Client HTTP pour effectuer des requêtes vers l'API
    private HttpClientInterface $client;

    // Clé d'API pour s'authentifier auprès du service météo
    private string $apiKey;

    // Service de cache pour stocker les données météo
    private CacheInterface $cache;

    /**
     * Constructeur du service
     * 
     * @param HttpClientInterface $client Client HTTP injecté par Symfony
     * @param CacheInterface $cache Service de cache injecté par Symfony
     */
    public function __construct(HttpClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['RAPIDAPI_KEY']; // Récupération de la clé API depuis les variables d'environnement
        $this->cache = $cache;
    }

    /**
     * Récupère les données météorologiques pour une ville donnée
     * Met en cache les résultats pour éviter des appels API inutiles
     * 
     * @param string $city Le nom de la ville pour laquelle récupérer les données
     * @return array Les données météorologiques au format tableau
     * @throws \Exception Si aucun emplacement n'est trouvé pour la ville spécifiée
     */
    public function getWeatherData(string $city): array
    {
        // Nettoyage et validation de l'entrée utilisateur
        $city = trim($city); // Suppression des espaces superflus
        $city = htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); // Protection contre les injections XSS
        $city = mb_substr($city, 0, 50); // Limite la longueur du nom de ville

        // Création d'une clé de cache unique basée sur le nom de la ville
        $cacheKey = 'weather_data_' . strtolower($city);

        // Utilisation du cache pour éviter des appels API inutiles
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
            // Définit la durée de vie du cache à 12 heures (43 200 secondes)
            $item->expiresAfter(43200);

            // Étape 1: Récupération de l'ID de l'emplacement à partir du nom de la ville
            $locationUrl = 'https://ai-weather-by-meteosource.p.rapidapi.com/find_places?text=' . urlencode($city) . '&language=fr';
            $locationResponse = $this->client->request('GET', $locationUrl, [
                'headers' => [
                    'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey, // Authentification avec la clé API
                ]
            ]);

            // Décodage de la réponse JSON en tableau associatif
            $locationData = json_decode($locationResponse->getContent(), true);

            // Vérification qu'un emplacement a bien été trouvé
            if (empty($locationData)) {
                throw new \Exception('Aucun emplacement trouvé pour cette ville');
            }

            // Récupération de l'ID du premier résultat (le plus pertinent)
            $placeId = $locationData[0]['place_id'];

            // Étape 2: Récupération des données météorologiques pour l'emplacement trouvé
            $url = 'https://ai-weather-by-meteosource.p.rapidapi.com/daily?place_id=' . $placeId . '&language=fr&units=metric';
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey, // Authentification avec la clé API
                ]
            ]);

            // Conversion de la réponse en tableau et retour des données
            return $response->toArray();
        });
    }
    public function getWeatherCoord(?float $lat, ?float $lon): array
    {
        if ($lat === null || $lon === null) {
            throw new \Exception('Coordonnées manquantes');
        }

        $lat = round($lat, 4);
        $lon = round($lon, 4);
        $cacheKey = 'weather_coords_' . $lat . '_' . $lon;

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($lat, $lon) {
                $item->expiresAfter(43200); // 12h

                $url = "https://ai-weather-by-meteosource.p.rapidapi.com/daily?lat={$lat}&lon={$lon}&language=fr&units=metric";

                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'x-rapidapi-host' => 'ai-weather-by-meteosource.p.rapidapi.com',
                        'x-rapidapi-key' => $this->apiKey,
                    ]
                ]);

                return $response->toArray();
            });
        } catch (\Exception $e) {
            // On log l'erreur (optionnel) et on retourne un tableau vide pour éviter le 500
            // Si tu as un LoggerInterface, tu peux faire : $this->logger->error($e->getMessage());
            return [];
        }
    }
}
