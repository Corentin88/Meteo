<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\WeatherDataRepository;

/**
 * Entité représentant les données météorologiques d'une ville pour un jour donné
 * Cette entité est mappée à une table dans la base de données via Doctrine ORM
 */
#[ORM\Entity(repositoryClass: WeatherDataRepository::class)]
class WeatherData
{
    // ========== PROPRIÉTÉS ==========
    
    /**
     * Identifiant unique de l'entrée météo
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Nom de la ville concernée par les données météo
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $city = null;

    /**
     * Date des prévisions météorologiques
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $day = null;

    /**
     * Description textuelle de la météo (ex: "Ensoleillé", "Pluvieux")
     * @var string|null
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $weather = null;

    /**
     * Code ou URL de l'icône représentant la météo
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * Résumé détaillé des conditions météorologiques
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $summary = null;

    /**
     * Température actuelle en degrés Celsius
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperature = null;

    /**
     * Température minimale prévue pour la journée en degrés Celsius
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperatureMin = null;

    /**
     * Température maximale prévue pour la journée en degrés Celsius
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperatureMax = null;

    /**
     * Température ressentie en degrés Celsius
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $feelsLike = null;

    /**
     * Vitesse du vent en km/h
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $windSpeed = null;

    /**
     * Type de précipitation (ex: "pluie", "neige", "grêle")
     * @var string|null
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $precipitationType = null;

    /**
     * Probabilité de précipitation (valeur entre 0 et 1)
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityPrecipitation = null;

    /**
     * Probabilité d'orage (valeur entre 0 et 1)
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityStorm = null;

    /**
     * Probabilité de gel (valeur entre 0 et 1)
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityFreeze = null;

    /**
     * Taux d'humidité (valeur entre 0 et 1)
     * @var float|null
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $humidity = null;

    /**
     * Date et heure de la dernière mise à jour des données
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    // ========== MÉTHODES D'ACCÈS ==========
    
    /**
     * Obtient l'identifiant unique
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient le nom de la ville
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Définit le nom de la ville
     * @param string $city
     * @return self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Obtient la date des prévisions
     * @return \DateTimeInterface|null
     */
    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    /**
     * Définit la date des prévisions
     * @param \DateTimeInterface $day
     * @return self
     */
    public function setDay(\DateTimeInterface $day): self
    {
        $this->day = $day;
        return $this;
    }

    /**
     * Obtient la description textuelle de la météo
     * @return string|null
     */
    public function getWeather(): ?string
    {
        return $this->weather;
    }

    /**
     * Définit la description textuelle de la météo
     * @param string|null $weather
     * @return self
     */
    public function setWeather(?string $weather): self
    {
        $this->weather = $weather;
        return $this;
    }

    /**
     * Obtient le code ou URL de l'icône représentant la météo
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Définit le code ou URL de l'icône représentant la météo
     * @param string|null $icon
     * @return self
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Obtient le résumé détaillé des conditions météorologiques
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * Définit le résumé détaillé des conditions météorologiques
     * @param string|null $summary
     * @return self
     */
    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * Obtient la température actuelle en degrés Celsius
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    /**
     * Définit la température actuelle en degrés Celsius
     * @param float|null $temperature
     * @return self
     */
    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * Obtient la température minimale prévue pour la journée en degrés Celsius
     * @return float|null
     */
    public function getTemperatureMin(): ?float
    {
        return $this->temperatureMin;
    }

    /**
     * Définit la température minimale prévue pour la journée en degrés Celsius
     * @param float|null $temperatureMin
     * @return self
     */
    public function setTemperatureMin(?float $temperatureMin): self
    {
        $this->temperatureMin = $temperatureMin;
        return $this;
    }

    /**
     * Obtient la température maximale prévue pour la journée en degrés Celsius
     * @return float|null
     */
    public function getTemperatureMax(): ?float
    {
        return $this->temperatureMax;
    }

    /**
     * Définit la température maximale prévue pour la journée en degrés Celsius
     * @param float|null $temperatureMax
     * @return self
     */
    public function setTemperatureMax(?float $temperatureMax): self
    {
        $this->temperatureMax = $temperatureMax;
        return $this;
    }

    /**
     * Obtient la température ressentie en degrés Celsius
     * @return float|null
     */
    public function getFeelsLike(): ?float
    {
        return $this->feelsLike;
    }

    /**
     * Définit la température ressentie en degrés Celsius
     * @param float|null $feelsLike
     * @return self
     */
    public function setFeelsLike(?float $feelsLike): self
    {
        $this->feelsLike = $feelsLike;
        return $this;
    }

    /**
     * Obtient la vitesse du vent en km/h
     * @return float|null
     */
    public function getWindSpeed(): ?float
    {
        return $this->windSpeed;
    }

    /**
     * Définit la vitesse du vent en km/h
     * @param float|null $windSpeed
     * @return self
     */
    public function setWindSpeed(?float $windSpeed): self
    {
        $this->windSpeed = $windSpeed;
        return $this;
    }

    /**
     * Obtient le type de précipitation (ex: "pluie", "neige", "grêle")
     * @return string|null
     */
    public function getPrecipitationType(): ?string
    {
        return $this->precipitationType;
    }

    /**
     * Définit le type de précipitation (ex: "pluie", "neige", "grêle")
     * @param string|null $precipitationType
     * @return self
     */
    public function setPrecipitationType(?string $precipitationType): self
    {
        $this->precipitationType = $precipitationType;
        return $this;
    }

    /**
     * Obtient la probabilité de précipitation (valeur entre 0 et 1)
     * @return float|null
     */
    public function getProbabilityPrecipitation(): ?float
    {
        return $this->probabilityPrecipitation;
    }

    /**
     * Définit la probabilité de précipitation (valeur entre 0 et 1)
     * @param float|null $probabilityPrecipitation
     * @return self
     */
    public function setProbabilityPrecipitation(?float $probabilityPrecipitation): self
    {
        $this->probabilityPrecipitation = $probabilityPrecipitation;
        return $this;
    }

    /**
     * Obtient la probabilité d'orage (valeur entre 0 et 1)
     * @return float|null
     */
    public function getProbabilityStorm(): ?float
    {
        return $this->probabilityStorm;
    }

    /**
     * Définit la probabilité d'orage (valeur entre 0 et 1)
     * @param float|null $probabilityStorm
     * @return self
     */
    public function setProbabilityStorm(?float $probabilityStorm): self
    {
        $this->probabilityStorm = $probabilityStorm;
        return $this;
    }

    /**
     * Obtient la probabilité de gel (valeur entre 0 et 1)
     * @return float|null
     */
    public function getProbabilityFreeze(): ?float
    {
        return $this->probabilityFreeze;
    }

    /**
     * Définit la probabilité de gel (valeur entre 0 et 1)
     * @param float|null $probabilityFreeze
     * @return self
     */
    public function setProbabilityFreeze(?float $probabilityFreeze): self
    {
        $this->probabilityFreeze = $probabilityFreeze;
        return $this;
    }

    /**
     * Obtient le taux d'humidité (valeur entre 0 et 1)
     * @return float|null
     */
    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    /**
     * Définit le taux d'humidité (valeur entre 0 et 1)
     * @param float|null $humidity
     * @return self
     */
    public function setHumidity(?float $humidity): self
    {
        $this->humidity = $humidity;
        return $this;
    }

    /**
     * Obtient la date et heure de la dernière mise à jour des données
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Définit la date et heure de la dernière mise à jour des données
     * @param \DateTimeInterface $updatedAt
     * @return self
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
