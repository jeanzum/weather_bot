<?php

namespace Tests\Unit\Actions;

use App\Actions\ProcessChatMessageAction;
use App\Contracts\LlmServiceInterface;
use App\Contracts\WeatherServiceInterface;
use App\DTOs\ChatMessageDTO;
use App\Models\Conversation;
use App\Models\Message;
use App\Enums\MessageRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class ProcessChatMessageActionTest extends TestCase
{
    use RefreshDatabase;

    private ProcessChatMessageAction $action;
    private $mockLlmService;
    private $mockWeatherService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockLlmService = Mockery::mock(LlmServiceInterface::class);
        $this->mockWeatherService = Mockery::mock(WeatherServiceInterface::class);
        
        $this->action = new ProcessChatMessageAction(
            $this->mockLlmService,
            $this->mockWeatherService
        );
    }

    public function test_processes_weather_query_successfully(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversationId = null;
        $userMessage = '¿Cómo está el clima en Madrid?';

        // Mock LLM service to detect weather query
        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->with($userMessage)
            ->andReturn(true);

        $this->mockLlmService
            ->shouldReceive('extractCityFromMessage')
            ->with($userMessage)
            ->andReturn('Madrid');

        // Mock weather service
        $weatherData = [
            'location' => 'Madrid',
            'temperature' => 22,
            'description' => 'Soleado',
            'humidity' => 45
        ];

        $this->mockWeatherService
            ->shouldReceive('getCurrentWeather')
            ->with('Madrid')
            ->andReturn($weatherData);

        // Mock LLM response
        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => true,
                'response' => 'El clima en Madrid está soleado con 22°C.'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversationId);
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('conversation_id', $result['data']);
        $this->assertArrayHasKey('user_message', $result['data']);
        $this->assertArrayHasKey('assistant_message', $result['data']);
        $this->assertTrue($result['data']['assistant_message']['weather_data_used']);
    }

    public function test_processes_general_query_without_weather(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversationId = null;
        $userMessage = '¿Qué es un huracán?';

        // Mock LLM service to detect no weather data needed
        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->with($userMessage)
            ->andReturn(false);

        // Mock LLM response
        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => true,
                'response' => 'Un huracán es un sistema de baja presión con vientos rotativos.'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversationId);
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['data']['assistant_message']['weather_data_used']);
    }

    public function test_handles_weather_service_error(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversationId = null;
        $userMessage = '¿Cómo está el clima en CiudadInexistente?';

        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->andReturn(true);

        $this->mockLlmService
            ->shouldReceive('extractCityFromMessage')
            ->andReturn('CiudadInexistente');

        // Mock weather service error
        $this->mockWeatherService
            ->shouldReceive('getCurrentWeather')
            ->andReturn(null);

        // Mock LLM response handling error
        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => true,
                'response' => 'No pude obtener información del clima para esa ciudad.'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversationId);
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['data']['assistant_message']['weather_data_used']);
    }

    public function test_handles_llm_service_error(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversationId = null;
        $userMessage = 'Test message';

        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->andReturn(false);

        // Mock LLM service error
        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => false,
                'error' => 'API temporalmente no disponible'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversationId);
        $result = $this->action->execute($dto);

        $this->assertFalse($result['success']);
        $this->assertEquals('API temporalmente no disponible', $result['message']);
    }

    public function test_creates_new_conversation_when_none_exists(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversationId = null;
        $userMessage = 'Hola';

        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->andReturn(false);

        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => true,
                'response' => '¡Hola! Soy tu asistente meteorológico.'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversationId);
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('conversations', [
            'session_uuid' => $sessionUuid
        ]);
    }

    public function test_uses_existing_conversation_when_provided(): void
    {
        $sessionUuid = 'test-session-uuid';
        $conversation = Conversation::factory()->create(['session_uuid' => $sessionUuid]);
        $userMessage = 'Segundo mensaje';

        $this->mockLlmService
            ->shouldReceive('needsWeatherData')
            ->andReturn(false);

        $this->mockLlmService
            ->shouldReceive('generateResponse')
            ->andReturn([
                'success' => true,
                'response' => 'Respuesta al segundo mensaje.'
            ]);

        $dto = new ChatMessageDTO($sessionUuid, $userMessage, $conversation->id);
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertEquals($conversation->id, $result['data']['conversation_id']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
