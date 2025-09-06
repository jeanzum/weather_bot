<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    private WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = new WeatherService();
    }

    public function test_can_get_current_weather_with_valid_city(): void
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/v1/search*' => Http::response([
                'results' => [
                    [
                        'latitude' => 4.7110,
                        'longitude' => -74.0721
                    ]
                ]
            ], 200),
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'current' => [
                    'temperature_2m' => 18.5,
                    'apparent_temperature' => 17.2,
                    'relative_humidity_2m' => 78,
                    'precipitation' => 0.1,
                    'wind_speed_10m' => 5.4,
                    'weather_code' => 61
                ],
                'current_units' => [
                    'temperature_2m' => '°C',
                    'precipitation' => 'mm',
                    'wind_speed_10m' => 'km/h'
                ]
            ], 200)
        ]);

        $result = $this->weatherService->getCurrentWeather('Bogotá');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('location', $result);
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('weather_description', $result);
        $this->assertEquals('Bogotá', $result['location']);
        $this->assertEquals(18.5, $result['temperature']);
        $this->assertEquals('Lluvia ligera', $result['weather_description']);
    }

    public function test_throws_exception_when_city_not_found(): void
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/v1/search*' => Http::response([
                'results' => []
            ], 200)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se pudo encontrar la ciudad: CiudadInexistente');

        $this->weatherService->getCurrentWeather('CiudadInexistente');
    }

    public function test_can_get_weather_forecast(): void
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/v1/search*' => Http::response([
                'results' => [
                    [
                        'latitude' => 40.4168,
                        'longitude' => -3.7038
                    ]
                ]
            ], 200),
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'daily' => [
                    'time' => ['2024-01-01', '2024-01-02', '2024-01-03'],
                    'temperature_2m_max' => [22.5, 24.1, 19.8],
                    'temperature_2m_min' => [12.3, 14.7, 11.2],
                    'precipitation_probability_max' => [10, 65, 80],
                    'weather_code' => [1, 61, 95]
                ],
                'daily_units' => [
                    'temperature_2m_max' => '°C',
                    'temperature_2m_min' => '°C'
                ]
            ], 200)
        ]);

        $result = $this->weatherService->getForecast('Madrid', 3);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('location', $result);
        $this->assertArrayHasKey('forecast', $result);
        $this->assertCount(3, $result['forecast']);
        
        $firstDay = $result['forecast'][0];
        $this->assertArrayHasKey('date', $firstDay);
        $this->assertArrayHasKey('max_temp', $firstDay);
        $this->assertArrayHasKey('min_temp', $firstDay);
        $this->assertArrayHasKey('weather_description', $firstDay);
        $this->assertEquals('Principalmente despejado', $firstDay['weather_description']);
    }

    public function test_handles_api_errors_gracefully(): void
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/v1/search*' => Http::response([
                'results' => [
                    [
                        'latitude' => 4.7110,
                        'longitude' => -74.0721
                    ]
                ]
            ], 200),
            'https://api.open-meteo.com/v1/forecast*' => Http::response([], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error al obtener datos del clima');

        $this->weatherService->getCurrentWeather('Bogotá');
    }
}
