<?php

namespace App\Contracts;

interface LlmServiceInterface
{
    /**
     * Determine if a message needs weather data
     *
     * @param string $message
     * @return bool
     * @throws \Exception
     */
    public function needsWeatherData(string $message): bool;

    /**
     * Extract city name from a message
     *
     * @param string $message
     * @return string|null
     * @throws \Exception
     */
    public function extractCityFromMessage(string $message): ?string;

    /**
     * Generate a response using the LLM
     *
     * @param string $message
     * @param array|null $weatherData
     * @param array $conversationHistory
     * @param bool $isFirstMessage
     * @return array
     * @throws \Exception
     */
    public function generateResponse(string $message, ?array $weatherData, array $conversationHistory, bool $isFirstMessage = false): array;
}