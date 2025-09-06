<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_uuid' => Str::uuid()->toString(),
            'title' => $this->faker->sentence(3),
            'last_message' => $this->faker->sentence(),
            'last_message_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}