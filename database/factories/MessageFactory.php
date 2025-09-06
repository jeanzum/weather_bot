<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'metadata' => null,
        ];
    }

    public function userMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'content' => $this->faker->sentence(),
        ]);
    }

    public function assistantMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'content' => $this->faker->paragraph(),
            'metadata' => ['weather_data_used' => $this->faker->boolean()],
        ]);
    }
}