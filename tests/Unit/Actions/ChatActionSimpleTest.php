<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\ProcessChatMessageAction;
use App\DTOs\ChatMessageDTO;
use App\Contracts\LlmServiceInterface;
use App\Contracts\WeatherServiceInterface;
use Mockery;

class ChatActionSimpleTest extends TestCase
{
    public function test_it_works(): void
    {
        $llmService = Mockery::mock(LlmServiceInterface::class);
        $weatherService = Mockery::mock(WeatherServiceInterface::class);
        
        $this->app->instance(LlmServiceInterface::class, $llmService);
        $this->app->instance(WeatherServiceInterface::class, $weatherService);
        
        $llmService->shouldReceive('needsWeatherData')->andReturn(false);
        $llmService->shouldReceive('generateResponse')->andReturn([
            'success' => true,
            'response' => 'Hi there!'
        ]);

        $action = new ProcessChatMessageAction();
        $dto = new ChatMessageDTO('hello', 'test_session');
        $result = $action->execute($dto);

        $this->assertTrue($result['success']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
