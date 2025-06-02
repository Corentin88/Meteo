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
    private ?string $city = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $day = null;

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
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(\DateTimeInterface $day): self
    {
        $this->day = $day;
        return $this;
    }

    public function getWeather(): ?string
    {
        return $this->weather;
    }

    public function setWeather(?string $weather): self
    {
        $this->weather = $weather;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getTemperatureMin(): ?float
    {
        return $this->temperatureMin;
    }

    public function setTemperatureMin(?float $temperatureMin): self
    {
        $this->temperatureMin = $temperatureMin;
        return $this;
    }

    public function getTemperatureMax(): ?float
    {
        return $this->temperatureMax;
    }

    public function setTemperatureMax(?float $temperatureMax): self
    {
        $this->temperatureMax = $temperatureMax;
        return $this;
    }

    public function getFeelsLike(): ?float
    {
        return $this->feelsLike;
    }

    public function setFeelsLike(?float $feelsLike): self
    {
        $this->feelsLike = $feelsLike;
        return $this;
    }

    public function getWindSpeed(): ?float
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(?float $windSpeed): self
    {
        $this->windSpeed = $windSpeed;
        return $this;
    }

    public function getPrecipitationType(): ?string
    {
        return $this->precipitationType;
    }

    public function setPrecipitationType(?string $precipitationType): self
    {
        $this->precipitationType = $precipitationType;
        return $this;
    }

    public function getProbabilityPrecipitation(): ?float
    {
        return $this->probabilityPrecipitation;
    }

    public function setProbabilityPrecipitation(?float $probabilityPrecipitation): self
    {
        $this->probabilityPrecipitation = $probabilityPrecipitation;
        return $this;
    }

    public function getProbabilityStorm(): ?float
    {
        return $this->probabilityStorm;
    }

    public function setProbabilityStorm(?float $probabilityStorm): self
    {
        $this->probabilityStorm = $probabilityStorm;
        return $this;
    }

    public function getProbabilityFreeze(): ?float
    {
        return $this->probabilityFreeze;
    }

    public function setProbabilityFreeze(?float $probabilityFreeze): self
    {
        $this->probabilityFreeze = $probabilityFreeze;
        return $this;
    }

    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    public function setHumidity(?float $humidity): self
    {
        $this->humidity = $humidity;
        return $this;
    }
}
