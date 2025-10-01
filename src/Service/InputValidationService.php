<?php

namespace App\Service;

class InputValidationService
{
    /**
     * Valide et nettoie le nom de ville
     */
    public function validateCity(?string $city): string
    {
        if (!$city) {
            throw new \InvalidArgumentException('Le nom de ville est requis');
        }

        $city = trim($city);
        
        if (strlen($city) < 2 || strlen($city) > 50) {
            throw new \InvalidArgumentException('Le nom de ville doit contenir entre 2 et 50 caractères');
        }

        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $city)) {
            throw new \InvalidArgumentException('Le nom de ville contient des caractères invalides');
        }

        return htmlspecialchars($city, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valide les coordonnées GPS
     */
    public function validateCoordinates(?string $lat, ?string $lon): ?array
    {
        if ($lat === null || $lon === null) {
            return null;
        }

        if (!is_numeric($lat) || !is_numeric($lon)) {
            throw new \InvalidArgumentException('Coordonnées invalides');
        }

        $latFloat = (float) $lat;
        $lonFloat = (float) $lon;

        if ($latFloat < -90 || $latFloat > 90) {
            throw new \InvalidArgumentException('Latitude invalide (doit être entre -90 et 90)');
        }

        if ($lonFloat < -180 || $lonFloat > 180) {
            throw new \InvalidArgumentException('Longitude invalide (doit être entre -180 et 180)');
        }

        return ['lat' => $latFloat, 'lon' => $lonFloat];
    }
}