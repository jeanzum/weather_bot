<?php

namespace App\Services;

use App\Contracts\WeatherServiceInterface;
use App\DTOs\WeatherDataDTO;
use App\Enums\WeatherErrorCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService implements WeatherServiceInterface
{
    private const BASE_URL = 'https://api.open-meteo.com/v1';
    private const GEOCODING_URL = 'https://geocoding-api.open-meteo.com/v1';

    public function getCurrentWeather(string $city): array
    {
        try {
            
            $coordinates = $this->getCoordinates($city);
            if (!$coordinates) {
                throw new \Exception(WeatherErrorCode::CITY_NOT_FOUND->value);
            }


            $response = Http::timeout(10)->get(self::BASE_URL . '/forecast', [
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lon'],
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                'timezone' => 'auto',
            ]);

            if (!$response->successful()) {
                Log::error("🌡️ WeatherService: API error - Status: {$response->status()}, Body: " . $response->body());
                
                switch ($response->status()) {
                    case 400:
                        throw new \Exception(WeatherErrorCode::INVALID_PARAMETERS->value);
                    case 429:
                        throw new \Exception(WeatherErrorCode::RATE_LIMIT_EXCEEDED->value);
                    case 500:
                        throw new \Exception(WeatherErrorCode::WEATHER_SERVER_ERROR->value);
                    default:
                        throw new \Exception(WeatherErrorCode::WEATHER_API_ERROR->value);
                }
            }

            $data = $response->json();
            
            if (!isset($data['current'])) {
                Log::error("🌡️ WeatherService: Invalid response structure: " . json_encode($data));
                throw new \Exception(WeatherErrorCode::INVALID_RESPONSE->value);
            }
            
            
            return WeatherDataDTO::fromApiResponse($data, $city)->toArray();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("🌡️ WeatherService: Connection error: " . $e->getMessage());
            throw new \Exception(WeatherErrorCode::NO_INTERNET_CONNECTION->value);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("🌡️ WeatherService: Request error: " . $e->getMessage());
            throw new \Exception(WeatherErrorCode::API_TIMEOUT->value);
        } catch (\Exception $e) {
            Log::error("🌡️ WeatherService: General error: " . $e->getMessage());
            
            // Si ya es un error específico, no lo envuelvas
            $knownErrors = array_map(fn($case) => $case->value, WeatherErrorCode::cases());
            
            if (in_array($e->getMessage(), $knownErrors)) {
                throw $e;
            }
            
            throw new \Exception(WeatherErrorCode::GENERAL_WEATHER_ERROR->value);
        }
    }

    public function getForecast(string $city, int $days = 3): array
    {
        try {
            $coordinates = $this->getCoordinates($city);
            if (!$coordinates) {
                throw new \Exception("No se pudo encontrar la ciudad: {$city}");
            }

            $response = Http::get(self::BASE_URL . '/forecast', [
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lon'],
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_probability_max,weather_code',
                'forecast_days' => $days,
                'timezone' => 'auto',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error al obtener pronóstico del clima');
            }

            $data = $response->json();
            $forecast = [];

            for ($i = 0; $i < $days; $i++) {
                $forecast[] = [
                    'date' => $data['daily']['time'][$i],
                    'max_temp' => $data['daily']['temperature_2m_max'][$i],
                    'min_temp' => $data['daily']['temperature_2m_min'][$i],
                    'precipitation_probability' => $data['daily']['precipitation_probability_max'][$i],
                    'weather_code' => $data['daily']['weather_code'][$i],
                    'weather_description' => $this->getWeatherDescription($data['daily']['weather_code'][$i]),
                ];
            }

            return [
                'location' => $city,
                'forecast' => $forecast,
                'units' => $data['daily_units'],
            ];
        } catch (\Exception $e) {
            Log::error('WeatherService forecast error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getCoordinates(string $city): ?array
    {
        try {
            $response = Http::get(self::GEOCODING_URL . '/search', [
                'name' => $city,
                'count' => 1,
                'language' => 'es',
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            if (empty($data['results'])) {
                return null;
            }

            return [
                'lat' => $data['results'][0]['latitude'],
                'lon' => $data['results'][0]['longitude'],
            ];
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    private function getWeatherDescription(int $code): string
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