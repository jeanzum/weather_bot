<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Message;
use App\Enums\MessageRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_creation(): void
    {
        $sessionUuid = Str::uuid()->toString();
        
        $conversation = Conversation::create([
            'session_uuid' => $sessionUuid,
            'title' => 'Chat about weather',
            'last_message' => 'Hello'
        ]);

        $this->assertDatabaseHas('conversations', [
            'session_uuid' => $sessionUuid,
            'title' => 'Chat about weather'
        ]);
    }

    public function test_conversation_messages_relationship(): void
    {
        $conv = Conversation::factory()->create();

        $userMsg = Message::factory()->create([
            'conversation_id' => $conv->id,
            'role' => MessageRole::USER,
            'content' => 'clima madrid'
        ]);

        $assistantMsg = Message::factory()->create([
            'conversation_id' => $conv->id,
            'role' => MessageRole::ASSISTANT,
            'content' => 'En Madrid hace sol'
        ]);

        $this->assertCount(2, $conv->messages);
        $this->assertTrue($conv->messages->contains($userMsg));
        $this->assertTrue($conv->messages->contains($assistantMsg));
    }

    public function test_message_belongs_to_conversation(): void
    {
        $conv = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conv->id]);

        $this->assertEquals($conv->id, $message->conversation->id);
    }

    public function test_message_roles(): void
    {
        $userMsg = Message::factory()->create(['role' => MessageRole::USER]);
        $assistantMsg = Message::factory()->create(['role' => MessageRole::ASSISTANT]);

        $this->assertEquals(MessageRole::USER, $userMsg->role);
        $this->assertEquals(MessageRole::ASSISTANT, $assistantMsg->role);
    }

    public function test_weather_data_storage(): void
    {
        $weatherData = [
            'location' => 'Madrid',
            'temperature' => 22.5,
            'description' => 'Sunny'
        ];

        $message = Message::factory()->create([
            'weather_data_used' => $weatherData
        ]);

        $this->assertEquals($weatherData, $message->weather_data_used);
    }

    public function test_conversation_ordering(): void
    {
        $conv = Conversation::factory()->create();

        // Create messages with different timestamps
        $firstMsg = Message::factory()->create([
            'conversation_id' => $conv->id,
            'content' => 'First',
            'created_at' => now()->subMinutes(2)
        ]);

        $secondMsg = Message::factory()->create([
            'conversation_id' => $conv->id,
            'content' => 'Second',
            'created_at' => now()->subMinutes(1)
        ]);

        $orderedMessages = $conv->messages()->orderBy('created_at')->get();

        $this->assertEquals('First', $orderedMessages->first()->content);
        $this->assertEquals('Second', $orderedMessages->last()->content);
    }
}
