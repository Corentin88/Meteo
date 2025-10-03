<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Service\WeatherRetrievalService;
use App\Service\WeatherFormattingService;
use App\Service\InputValidationService;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class MeteoController extends AbstractController
{
    public function __construct(
        private WeatherRetrievalService $weatherRetrievalService,
        private WeatherFormattingService $weatherFormattingService,
        private InputValidationService $inputValidationService,
        private LoggerInterface $logger,
        private RateLimiterFactory $weatherSearchLimiter
    ) {}

    #[Route('/', name: 'meteo')]
    public function index(Request $request): Response
    {
        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'index, follow');
        // Récupération de l'IP du client
        $clientIp = $request->getClientIp();

        // Création d'un limiter pour cette IP
        $limiter = $this->weatherSearchLimiter->create($clientIp);

        // Tentative de consommation d'un token
        $limit = $limiter->consume(1);

        // Si la limite est atteinte
        if (!$limit->isAccepted()) {
            $this->addFlash('error', 'Trop de requêtes. Veuillez patienter quelques instants.');

            // Log de l'abus potentiel
            $this->logger->warning('Rate limit atteint', [
                'ip' => $clientIp,
                'retry_after' => $limit->getRetryAfter()->getTimestamp()
            ]);

            // Retourne une erreur 429 avec le temps d'attente
            return $this->render('meteo/rate_limit.html.twig', [
                'retry_after' => $limit->getRetryAfter()
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
        }
        try {
            // Validation et récupération des paramètres
            $coords = $this->inputValidationService->validateCoordinates(
                $request->query->get('lat'),
                $request->query->get('lon')
            );

            // Récupération des données météo
            if ($coords) {
                $result = $this->weatherRetrievalService->getWeatherByCoordinates(
                    $coords['lat'],
                    $coords['lon']
                );
            } else {
                $city = $this->inputValidationService->validateCity(
                    $request->query->get('ville', 'Nancy')
                );
                $result = $this->weatherRetrievalService->getWeatherByCity($city);
            }

            // Normalisation et formatage des données
            $dailyData = $this->weatherFormattingService->normalizeWeatherData($result['data']);

            if (empty($dailyData)) {
                throw new \RuntimeException("Aucune donnée météo disponible");
            }

            $dailyData = $this->weatherFormattingService->formatDates($dailyData);
            $todayWeather = $dailyData[0] ?? null;

            if (!$todayWeather) {
                throw new \RuntimeException("Données du jour manquantes");
            }

            // Préparation des données pour le template
            $templateData = $this->weatherFormattingService->prepareTemplateData($todayWeather, $dailyData);
            $templateData['ville'] = $result['city'];

            return $this->render('meteo/index.html.twig', $templateData, $response);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('meteo', ['ville' => 'Nancy']);
        } catch (\Exception $e) {
            $this->logger->error('Erreur météo : ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la récupération des données météo');
            return $this->redirectToRoute('meteo', ['ville' => 'Nancy']);
        }
    }
}
