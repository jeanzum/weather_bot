<?php

namespace Tests\Unit;

use App\Services\LlmService;
use App\Contracts\LlmServiceInterface;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmServiceTest extends TestCase
{
    private LlmService $llmService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->llmService = new LlmService();
    }

    public function test_implements_llm_service_interface(): void
    {
        $this->assertInstanceOf(LlmServiceInterface::class, $this->llmService);
    }

    public function test_generates_response_successfully(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'El clima en Madrid es soleado con 22°C.'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->llmService->generateResponse('¿Cómo está el clima en Madrid?');

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['response']);
        $this->assertStringContainsString('Madrid', $result['response']);
    }

    public function test_handles_api_connection_error(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::throw(new \Illuminate\Http\Client\ConnectionException('Connection failed'))
        ]);

        $result = $this->llmService->generateResponse('Test message');

        $this->assertFalse($result['success']);
        $this->assertNull($result['response']);
        $this->assertStringContainsString('conexión', $result['error']);
    }

    public function test_handles_api_timeout_error(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::throw(new \Illuminate\Http\Client\RequestException(
                new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(408))
            ))
        ]);

        $result = $this->llmService->generateResponse('Test message');

        $this->assertFalse($result['success']);
        $this->assertNull($result['response']);
        $this->assertStringContainsString('tardando', $result['error']);
    }

    public function test_detects_weather_query_needs_data(): void
    {
        // Casos que SÍ necesitan datos meteorológicos
        $this->assertTrue($this->llmService->needsWeatherData('¿Cómo está el clima en Barcelona?'));
        $this->assertTrue($this->llmService->needsWeatherData('Temperatura en Madrid'));
        $this->assertTrue($this->llmService->needsWeatherData('¿Lloverá mañana en Bogotá?'));
        
        // Casos que NO necesitan datos meteorológicos
        $this->assertFalse($this->llmService->needsWeatherData('¿Qué es un huracán?'));
        $this->assertFalse($this->llmService->needsWeatherData('Explícame cómo se forman las nubes'));
        $this->assertFalse($this->llmService->needsWeatherData('Hola'));
    }

    public function test_extracts_city_from_message(): void
    {
        $this->assertEquals('Madrid', $this->llmService->extractCityFromMessage('¿Cómo está el clima en Madrid?'));
        $this->assertEquals('Barcelona', $this->llmService->extractCityFromMessage('Temperatura de Barcelona'));
        $this->assertEquals('Bogotá', $this->llmService->extractCityFromMessage('clima bogotá'));
        $this->assertNull($this->llmService->extractCityFromMessage('¿Qué es el viento?'));
    }

    public function test_sanitizes_user_input(): void
    {
        $reflection = new \ReflectionClass($this->llmService);
        $method = $reflection->getMethod('sanitizeUserInput');
        $method->setAccessible(true);

        // Test normal input
        $result = $method->invoke($this->llmService, '¿Cómo está el clima?');
        $this->assertEquals('¿Cómo está el clima?', $result);

        // Test input with extra whitespace
        $result = $method->invoke($this->llmService, "  Clima   en   Madrid  \n\n  ");
        $this->assertEquals('Clima en Madrid', $result);

        // Test very long input (should be truncated)
        $longMessage = str_repeat('a', 1500);
        $result = $method->invoke($this->llmService, $longMessage);
        $this->assertLessThanOrEqual(1003, strlen($result)); // 1000 + "..."
    }

    public function test_validates_output_removes_sensitive_data(): void
    {
        $reflection = new \ReflectionClass($this->llmService);
        $method = $reflection->getMethod('validateOutput');
        $method->setAccessible(true);

        // Test normal output
        $result = $method->invoke($this->llmService, 'El clima está soleado');
        $this->assertEquals('El clima está soleado', $result);

        // Test output with sensitive data
        $sensitiveOutput = 'Tu API KEY es sk-abc123 y tu password es secret';
        $result = $method->invoke($this->llmService, $sensitiveOutput);
        $this->assertStringContainsString('problema técnico', $result);
    }

    public function test_handles_missing_api_key(): void
    {
        // Temporarily remove API key
        config(['services.openai.api_key' => '']);
        $llmService = new LlmService();

        $result = $llmService->generateResponse('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('API key', $result['error']);
    }

    public function test_handles_empty_api_response(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => ''
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->llmService->generateResponse('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('vacía', $result['error']);
    }
}
