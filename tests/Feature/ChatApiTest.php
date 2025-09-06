<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_send_message_to_new_conversation(): void
    {
        $response = $this->postJson('/api/v1/chat/message', [
            'message' => '¿Cómo está el clima en Bogotá?',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversation_id',
                    'user_message' => [
                        'id', 'content', 'role', 'created_at'
                    ],
                    'assistant_message' => [
                        'id', 'content', 'role', 'created_at', 'weather_data_used'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'content' => '¿Cómo está el clima en Bogotá?',
            'role' => 'user',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'role' => 'assistant',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_send_message_to_existing_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/v1/chat/message', [
            'message' => 'Gracias por la información',
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(200);
        
        $this->assertEquals($conversation->id, $response->json('data.conversation_id'));
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_cannot_send_empty_message(): void
    {
        $response = $this->postJson('/api/v1/chat/message', [
            'message' => '',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_cannot_send_message_with_invalid_user(): void
    {
        $response = $this->postJson('/api/v1/chat/message', [
            'message' => 'Hola',
            'user_id' => 999,
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_can_get_user_conversations(): void
    {
        $conversations = Conversation::factory(3)->create([
            'user_id' => $this->user->id,
        ]);

        $otherUserConversation = Conversation::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->getJson("/api/v1/chat/conversations?user_id={$this->user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id', 'title', 'last_message_at', 'last_message', 'messages_count'
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_get_specific_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $messages = Message::factory(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/chat/conversations/{$conversation->id}?user_id={$this->user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'title', 'created_at', 'last_message_at',
                    'messages' => [
                        '*' => [
                            'id', 'content', 'role', 'created_at', 'weather_data_used'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(5, $response->json('data.messages'));
    }

    public function test_cannot_access_other_users_conversation(): void
    {
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson("/api/v1/chat/conversations/{$conversation->id}?user_id={$this->user->id}");

        $response->assertStatus(404);
    }

    public function test_can_delete_conversation(): void
    {
        $conversation = Conversation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $messages = Message::factory(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/chat/conversations/{$conversation->id}?user_id={$this->user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Conversación eliminada exitosamente'
            ]);

        $this->assertDatabaseMissing('conversations', [
            'id' => $conversation->id,
        ]);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
        ]);
    }

    public function test_weather_service_integration(): void
    {
        $response = $this->postJson('/api/v1/chat/message', [
            'message' => '¿Va a llover hoy en Madrid?',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(200);
        
        $assistantMessage = $response->json('data.assistant_message');
        $this->assertNotEmpty($assistantMessage['content']);
        $this->assertIsString($assistantMessage['content']);
    }
}