<?php

namespace App\DTOs;

readonly class ChatMessageDTO
{
    public function __construct(
        public string $message,
        public string $sessionUuid,
        public ?int $conversationId = null,
        public ?WeatherDataDTO $weatherData = null,
        public array $conversationHistory = []
    ) {}

    public function withWeatherData(WeatherDataDTO $weatherData): self
    {
        return new self(
            message: $this->message,
            sessionUuid: $this->sessionUuid,
            conversationId: $this->conversationId,
            weatherData: $weatherData,
            conversationHistory: $this->conversationHistory
        );
    }

    public function withConversationHistory(array $history): self
    {
        return new self(
            message: $this->message,
            sessionUuid: $this->sessionUuid,
            conversationId: $this->conversationId,
            weatherData: $this->weatherData,
            conversationHistory: $history
        );
    }
}