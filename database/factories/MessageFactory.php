<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'session_uuid' => Str::uuid()->toString(),
            'content' => $this->faker->paragraph(),
            'role' => $this->faker->randomElement([MessageRole::USER, MessageRole::ASSISTANT]),
            'weather_data_used' => [],
        ];
    }

    public function userMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::USER,
            'content' => $this->faker->sentence(),
            'weather_data_used' => [],
        ]);
    }

    public function assistantMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::ASSISTANT,
            'content' => $this->faker->paragraph(),
            'weather_data_used' => [],
        ]);
    }
}