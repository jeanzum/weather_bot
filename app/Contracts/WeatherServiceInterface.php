<?php

namespace App\Contracts;

interface WeatherServiceInterface
{
    /**
     * Get current weather data for a city
     *
     * @param string $city
     * @return array
     * @throws \Exception
     */
    public function getCurrentWeather(string $city): array;

    /**
     * Get weather forecast for multiple days
     *
     * @param string $city
     * @param int $days
     * @return array
     * @throws \Exception
     */
    public function getForecast(string $city, int $days = 3): array;
}