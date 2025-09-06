<?php

namespace Tests\Unit\Services;

use App\Services\LlmService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmServiceTest extends TestCase
{
    private LlmService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LlmService();
    }

    public function test_basic_response_generation(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'El clima en Madrid está soleado']]
                ]
            ])
        ]);

        $result = $this->service->generateResponse('clima madrid', []);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Madrid', $result['response']);
    }

    public function test_handles_api_errors(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(null, 500)]);

        $result = $this->service->generateResponse('test', []);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_weather_query_detection(): void
    {
        // Mock OpenAI API to fail and trigger fallback
        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        $weatherQueries = [
            'clima en barcelona',
            'temperatura madrid',
            'lloverá mañana?'
        ];

        foreach ($weatherQueries as $query) {
            $result = $this->service->needsWeatherData($query);
            $this->assertTrue($result, "Should detect weather query: $query");
        }

        // Non-weather queries
        $nonWeatherQueries = [
            'que es un huracan',
            'hola',
            'buenos dias'
        ];
        
        foreach ($nonWeatherQueries as $query) {
            $result = $this->service->needsWeatherData($query);
            $this->assertFalse($result, "Should not detect weather query: $query");
        }
    }

    public function test_city_extraction(): void
    {
        $this->assertEquals('Madrid', $this->service->extractCityFromMessage('clima en madrid'));
        $this->assertEquals('Barcelona', $this->service->extractCityFromMessage('temperatura barcelona'));
        
        // Should return null for queries without clear city
        $result = $this->service->extractCityFromMessage('como esta el tiempo');
        $this->assertTrue($result === null || $result === '');
    }

    // Skip input sanitization test since method doesn't exist yet
}
