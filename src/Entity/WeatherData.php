<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class WeatherData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $day = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $weather = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperature = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperatureMin = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperatureMax = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $feelsLike = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $windSpeed = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $precipitationType = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityPrecipitation = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityStorm = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $probabilityFreeze = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $humidity = null;

    public function getId(): ?int
    {
        return $id;
    }

    public function getDay(): ?string
    {
        return $day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;
        return $this;
    }

    public function getWeather(): ?string
    {
        return $weather;
    }

    public function setWeather(?string $weather): self
    {
        $this->weather = $weather;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getTemperature(): ?float
    {
        return $temperature;
    }

    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getTemperatureMin(): ?float
    {
        return $temperatureMin;
    }

    public function setTemperatureMin(?float $temperatureMin): self
    {
        $this->temperatureMin = $temperatureMin;
        return $this;
    }

    public function getTemperatureMax(): ?float
    {
        return $temperatureMax;
    }

    public function setTemperatureMax(?float $temperatureMax): self
    {
        $this->temperatureMax = $temperatureMax;
        return $this;
    }

    public function getFeelsLike(): ?float
    {
        return $feelsLike;
    }

    public function setFeelsLike(?float $feelsLike): self
    {
        $this->feelsLike = $feelsLike;
        return $this;
    }

    public function getWindSpeed(): ?float
    {
        return $windSpeed;
    }

    public function setWindSpeed(?float $windSpeed): self
    {
        $this->windSpeed = $windSpeed;
        return $this;
    }

    public function getPrecipitationType(): ?string
    {
        return $precipitationType;
    }

    public function setPrecipitationType(?string $precipitationType): self
    {
        $this->precipitationType = $precipitationType;
        return $this;
    }

    public function getProbabilityPrecipitation(): ?float
    {
        return $probabilityPrecipitation;
    }

    public function setProbabilityPrecipitation(?float $probabilityPrecipitation): self
    {
        $this->probabilityPrecipitation = $probabilityPrecipitation;
        return $this;
    }

    public function getProbabilityStorm(): ?float
    {
        return $probabilityStorm;
    }

    public function setProbabilityStorm(?float $probabilityStorm): self
    {
        $this->probabilityStorm = $probabilityStorm;
        return $this;
    }

    public function getProbabilityFreeze(): ?float
    {
        return $probabilityFreeze;
    }

    public function setProbabilityFreeze(?float $probabilityFreeze): self
    {
        $this->probabilityFreeze = $probabilityFreeze;
        return $this;
    }

    public function getHumidity(): ?float
    {
        return $humidity;
    }

    public function setHumidity(?float $humidity): self
    {
        $this->humidity = $humidity;
        return $this;
    }
}
