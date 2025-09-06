<?php

namespace Tests\Unit\Services;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    private WeatherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WeatherService();
    }

    public function test_gets_weather_data(): void
    {
        // Mock the geocoding response first
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    [
                        'latitude' => 4.6097,
                        'longitude' => -74.0817,
                        'name' => 'Bogotá'
                    ]
                ]
            ]),
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 18.5,
                    'relative_humidity_2m' => 75,
                    'wind_speed_10m' => 12.3,
                    'weather_code' => 61
                ]
            ])
        ]);

        $result = $this->service->getCurrentWeather('Bogotá');

        $this->assertIsArray($result);
        $this->assertEquals('Bogotá', $result['location']);
        $this->assertEquals(18.5, $result['temperature']);
    }

    public function test_city_not_found(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response(null, 404)]);

        $this->expectException(\Exception::class);
        $this->service->getCurrentWeather('CityNotFound123');
    }

    public function test_api_timeout(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $this->expectException(\Exception::class);
        $this->service->getCurrentWeather('Madrid');
    }

    public function test_weather_code_translation(): void
    {
        // Mock both geocoding and weather API
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    [
                        'latitude' => 40.4168,
                        'longitude' => -3.7038,
                        'name' => 'Madrid'
                    ]
                ]
            ]),
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 25.0,
                    'weather_code' => 0
                ]
            ])
        ]);

        $result = $this->service->getCurrentWeather('Madrid');
        
        $this->assertStringContainsString('despejado', strtolower($result['weather_description']));
    }
}
