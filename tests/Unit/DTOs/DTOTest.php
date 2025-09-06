<?php

namespace Tests\Unit\DTOs;

use App\DTOs\ChatMessageDTO;
use App\DTOs\WeatherDataDTO;
use Tests\TestCase;

class DTOTest extends TestCase
{
    public function test_creates_chat_message_dto(): void
    {
        $message = 'clima en madrid';
        $sessionUuid = 'sess_abc123';
        $conversationId = 456;

        $dto = new ChatMessageDTO($message, $sessionUuid, $conversationId);

        $this->assertEquals($message, $dto->message);
        $this->assertEquals($sessionUuid, $dto->sessionUuid);
        $this->assertEquals($conversationId, $dto->conversationId);
    }

    public function test_null_conversation_id(): void
    {
        $dto = new ChatMessageDTO('Test msg', 'sess_456', null);

        $this->assertNull($dto->conversationId);
    }

    public function test_weather_dto_basic(): void
    {
        $dto = new WeatherDataDTO(
            'Madrid',
            22.5,
            25.0,
            45,
            0.0,
            8.2,
            2,
            'Soleado'
        );

        $this->assertEquals('Madrid', $dto->location);
        $this->assertEquals(22.5, $dto->temperature);
        $this->assertEquals('Soleado', $dto->weatherDescription);
    }

    public function test_weather_to_array(): void
    {
        $dto = new WeatherDataDTO(
            'Barcelona',
            25.0,
            26.5,
            60,
            0.2,
            12.5,
            1,
            'Nublado'
        );

        $array = $dto->toArray();
        
        $this->assertEquals('Barcelona', $array['location']);
        $this->assertEquals(25.0, $array['temperature']);
        $this->assertEquals('Nublado', $array['weather_description']);
    }

    public function test_from_api_response(): void
    {
        $apiData = [
            'current' => [
                'temperature_2m' => 28.3,
                'apparent_temperature' => 30.1,
                'relative_humidity_2m' => 55,
                'precipitation' => 0.0,
                'wind_speed_10m' => 15.8,
                'weather_code' => 2
            ]
        ];

        $dto = WeatherDataDTO::fromApiResponse($apiData, 'Valencia');

        $this->assertEquals('Valencia', $dto->location);
        $this->assertEquals(28.3, $dto->temperature);
        $this->assertEquals(55, $dto->humidity);
    }

    public function test_weather_data_dto_from_api_response(): void
    {
        $apiResponse = [
            'current' => [
                'temperature_2m' => 28.3,
                'apparent_temperature' => 30.1,
                'relative_humidity_2m' => 55,
                'precipitation' => 0.0,
                'wind_speed_10m' => 15.8,
                'weather_code' => 2
            ]
        ];

        $dto = WeatherDataDTO::fromApiResponse($apiResponse, 'Valencia');

        $this->assertEquals('Valencia', $dto->location);
        $this->assertEquals(28.3, $dto->temperature);
        $this->assertEquals(30.1, $dto->feelsLike);
        $this->assertEquals(55, $dto->humidity);
        $this->assertEquals(0.0, $dto->precipitation);
        $this->assertEquals(15.8, $dto->windSpeed);
        $this->assertEquals(2, $dto->weatherCode);
    }

    public function test_handles_missing_data(): void
    {
        $dto = new WeatherDataDTO(
            'Sevilla',
            30.0,
            null,
            null,
            null,
            null,
            null,
            'Caluroso'
        );

        $this->assertEquals('Sevilla', $dto->location);
        $this->assertNull($dto->humidity);
        $this->assertNull($dto->windSpeed);
    }

    public function test_dto_readonly(): void
    {
        $dto = new ChatMessageDTO('original', 'sess_789', 456);

        $this->expectException(\Error::class);
        $dto->message = 'modified';
    }

    public function test_dto_immutability(): void
    {
        $dto = new ChatMessageDTO('Original message', 'session-123', 456);

        // DTOs should be immutable - changing properties should not be possible
        $this->expectException(\Error::class);
        $dto->message = 'Modified message';
    }

    public function test_json_serialization(): void
    {
        $dto = new WeatherDataDTO('Bilbao', 18.5, 19.2, 85, 2.3, 6.3, 3, 'Lluvioso');

        $json = json_encode($dto);
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertEquals('Bilbao', $decoded['location']);
        $this->assertEquals(18.5, $decoded['temperature']);
    }
}
