<?php

namespace App\DTOs;

readonly class ChatMessageDTO
{
    public function __construct(
        public string $message,
        public int $userId,
        public ?int $conversationId = null,
        public ?WeatherDataDTO $weatherData = null,
        public array $conversationHistory = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            message: $data['message'],
            userId: $data['user_id'],
            conversationId: $data['conversation_id'] ?? null
        );
    }

    public function withWeatherData(WeatherDataDTO $weatherData): self
    {
        return new self(
            message: $this->message,
            userId: $this->userId,
            conversationId: $this->conversationId,
            weatherData: $weatherData,
            conversationHistory: $this->conversationHistory
        );
    }

    public function withConversationHistory(array $history): self
    {
        return new self(
            message: $this->message,
            userId: $this->userId,
            conversationId: $this->conversationId,
            weatherData: $this->weatherData,
            conversationHistory: $history
        );
    }
}