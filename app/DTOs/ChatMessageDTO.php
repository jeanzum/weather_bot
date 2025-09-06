<?php

namespace App\DTOs;

readonly class ChatMessageDTO
{
    public function __construct(
        public string $message,
        public string $sessionUuid,
        public ?int $conversationId = null,
        public ?WeatherDataDTO $weatherData = null,
        public array $conversationHistory = [],
        public ?string $userCity = null,
        public ?float $userLatitude = null,
        public ?float $userLongitude = null
    ) {}

    public function withWeatherData(WeatherDataDTO $weatherData): self
    {
        return new self(
            message: $this->message,
            sessionUuid: $this->sessionUuid,
            conversationId: $this->conversationId,
            weatherData: $weatherData,
            conversationHistory: $this->conversationHistory,
            userCity: $this->userCity,
            userLatitude: $this->userLatitude,
            userLongitude: $this->userLongitude
        );
    }

    public function withConversationHistory(array $history): self
    {
        return new self(
            message: $this->message,
            sessionUuid: $this->sessionUuid,
            conversationId: $this->conversationId,
            weatherData: $this->weatherData,
            conversationHistory: $history,
            userCity: $this->userCity,
            userLatitude: $this->userLatitude,
            userLongitude: $this->userLongitude
        );
    }
}