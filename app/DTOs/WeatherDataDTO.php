<?php

namespace App\DTOs;

readonly class WeatherDataDTO
{
    public function __construct(
        public string $location,
        public ?float $temperature,
        public ?float $feelsLike,
        public ?int $humidity,
        public ?float $precipitation,
        public ?float $windSpeed,
        public ?int $weatherCode,
        public string $weatherDescription,
        public array $units = []
    ) {}

    public static function fromApiResponse(array $data, string $location): self
    {
        return new self(
            location: $location,
            temperature: $data['current']['temperature_2m'] ?? null,
            feelsLike: $data['current']['apparent_temperature'] ?? null,
            humidity: $data['current']['relative_humidity_2m'] ?? null,
            precipitation: $data['current']['precipitation'] ?? null,
            windSpeed: $data['current']['wind_speed_10m'] ?? null,
            weatherCode: $data['current']['weather_code'] ?? null,
            weatherDescription: self::getWeatherDescription($data['current']['weather_code'] ?? 0),
            units: $data['current_units'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'location' => $this->location,
            'temperature' => $this->temperature,
            'feels_like' => $this->feelsLike,
            'humidity' => $this->humidity,
            'precipitation' => $this->precipitation,
            'wind_speed' => $this->windSpeed,
            'weather_code' => $this->weatherCode,
            'weather_description' => $this->weatherDescription,
            'units' => $this->units,
        ];
    }

    private static function getWeatherDescription(int $code): string
    {
        $descriptions = [
            0 => 'Despejado',
            1 => 'Principalmente despejado',
            2 => 'Parcialmente nublado',
            3 => 'Nublado',
            45 => 'Neblina',
            48 => 'Neblina con escarcha',
            51 => 'Llovizna ligera',
            53 => 'Llovizna moderada',
            55 => 'Llovizna intensa',
            61 => 'Lluvia ligera',
            63 => 'Lluvia moderada',
            65 => 'Lluvia intensa',
            71 => 'Nevada ligera',
            73 => 'Nevada moderada',
            75 => 'Nevada intensa',
            80 => 'Chubascos ligeros',
            81 => 'Chubascos moderados',
            82 => 'Chubascos intensos',
            95 => 'Tormenta',
            96 => 'Tormenta con granizo ligero',
            99 => 'Tormenta con granizo intenso',
        ];

        return $descriptions[$code] ?? 'Condición meteorológica desconocida';
    }
}