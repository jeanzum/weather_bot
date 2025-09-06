<?php

namespace Database\Factories;

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
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'weather_data_used' => $this->faker->boolean(),
        ];
    }

    public function userMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'content' => $this->faker->sentence(),
            'weather_data_used' => false,
        ]);
    }

    public function assistantMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'content' => $this->faker->paragraph(),
            'weather_data_used' => $this->faker->boolean(),
        ]);
    }
}