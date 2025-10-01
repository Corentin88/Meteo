<?php

namespace App\Service;

class WeatherFormattingService
{
    private \IntlDateFormatter $formatter;

    public function __construct()
    {
        $this->formatter = new \IntlDateFormatter(
            'fr_FR', 
            \IntlDateFormatter::FULL, 
            \IntlDateFormatter::NONE
        );
    }

    /**
     * Normalise les données météo pour avoir toujours le même format
     */
    public function normalizeWeatherData(array $dailyData): array
    {
        // Gère les différents formats de réponse API
        if (isset($dailyData['daily']['data'])) {
            return $dailyData['daily']['data'];
        }
        
        if (isset($dailyData['daily'])) {
            return $dailyData['daily'];
        }
        
        return $dailyData;
    }

    /**
     * Formate les dates pour tous les jours
     */
    public function formatDates(array $dailyData): array
    {
        foreach ($dailyData as &$day) {
            $date = new \DateTime($day['day'] ?? 'now');
            $day['formattedDay'] = ucfirst($this->formatter->format($date));
        }
        
        return $dailyData;
    }

    /**
     * Prépare les données pour le template
     */
    public function prepareTemplateData(array $todayWeather, array $dailyData): array
    {
        return [
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
        ];
    }
}