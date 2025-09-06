<?php

namespace Tests\Unit\Actions;

use App\Actions\ProcessChatMessageAction;
use App\DTOs\ChatMessageDTO;
use App\Contracts\LlmServiceInterface;
use App\Contracts\WeatherServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatActionTest extends TestCase
{
    use RefreshDatabase;

    private ProcessChatMessageAction $action;
    private $llmService;
    private $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->llmService = Mockery::mock(LlmServiceInterface::class);
        $this->weatherService = Mockery::mock(WeatherServiceInterface::class);
        
        $this->action = new ProcessChatMessageAction(
            $this->llmService,
            $this->weatherService
        );
    }

    public function test_basic_chat_flow(): void
    {
        // Simple mock setup
        $this->llmService->shouldReceive('needsWeatherData')->andReturn(false);
        $this->llmService->shouldReceive('generateResponse')->andReturn([
            'success' => true,
            'response' => 'Hi! How can I help you?'
        ]);

        $dto = new ChatMessageDTO('hello', 'sess_123');
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('conversation_id', $result);
        $this->assertArrayHasKey('assistant_message', $result);
    }

    public function test_weather_query_flow(): void
    {
        $this->llmService->shouldReceive('needsWeatherData')->andReturn(true);
        $this->llmService->shouldReceive('extractCityFromMessage')->andReturn('Madrid');
        
        $this->weatherService->shouldReceive('getCurrentWeather')->andReturn([
            'location' => 'Madrid',
            'temperature' => 22.5,
            'weather_description' => 'Soleado'
        ]);

        $this->llmService->shouldReceive('generateResponse')->andReturn([
            'success' => true,
            'response' => 'En Madrid hace 22.5°C y está soleado'
        ]);

        $dto = new ChatMessageDTO('clima madrid', 'sess_456');
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['data']['assistant_message']['weather_data_used']);
    }

    public function test_handles_weather_api_failure(): void
    {
        $this->llmService->shouldReceive('needsWeatherData')->andReturn(true);
        $this->llmService->shouldReceive('extractCityFromMessage')->andReturn('Madrid');
        
        $this->weatherService->shouldReceive('getCurrentWeather')
            ->andThrow(new \Exception('API unavailable'));

        $this->llmService->shouldReceive('generateResponse')->andReturn([
            'success' => true,
            'response' => 'No puedo obtener el clima ahora'
        ]);

        $dto = new ChatMessageDTO('clima madrid', 'sess_789');
        $result = $this->action->execute($dto);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['data']['assistant_message']['weather_data_used']);
    }

    public function test_creates_conversation(): void
    {
        $this->llmService->shouldReceive('needsWeatherData')->andReturn(false);
        $this->llmService->shouldReceive('generateResponse')->andReturn([
            'success' => true,
            'response' => 'Response'
        ]);

        $dto = new ChatMessageDTO('test message', 'new_session_001');
        $result = $this->action->execute($dto);

        $this->assertDatabaseHas('conversations', [
            'session_uuid' => 'new_session_001'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
