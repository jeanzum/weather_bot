<?php

namespace App\Actions;

use App\Contracts\LlmServiceInterface;
use App\Contracts\WeatherServiceInterface;
use App\DTOs\ChatMessageDTO;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessChatMessageAction
{
    public function __construct(
        private LlmServiceInterface $llmService,
        private WeatherServiceInterface $weatherService
    ) {}

    public function execute(ChatMessageDTO $chatMessage): array
    {
        return DB::transaction(function () use ($chatMessage) {
            $conversation = $this->getOrCreateConversation($chatMessage);

            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'session_uuid' => $chatMessage->sessionUuid,
                'content' => $chatMessage->message,
                'role' => 'user'
            ]);

            $weatherData = null;
            $weatherError = null;
            $messageWithContext = $chatMessage->message;
            
            try {
                $weatherData = $this->getWeatherDataIfNeeded($chatMessage->message);
            } catch (\Exception $e) {
                $weatherError = $e->getMessage();
                $messageWithContext .= "\n\n[ERROR SERVICIO CLIMA]: " . $weatherError;
            }

            $conversationHistory = $this->getConversationHistory($conversation, 10);

            $llmResult = $this->llmService->generateResponse(
                $messageWithContext,
                $weatherData,
                $conversationHistory
            );

            if (!$llmResult['success']) {
                throw new \Exception($llmResult['error']);
            }

            $assistantMessage = Message::create([
                'conversation_id' => $conversation->id,
                'session_uuid' => $chatMessage->sessionUuid,
                'content' => $llmResult['response'],
                'role' => 'assistant',
                'weather_data_used' => $weatherData !== null
            ]);

            $conversation->updateLastMessage($llmResult['response']);

            if (!$conversation->title) {
                $conversation->update([
                    'title' => $this->generateConversationTitle($chatMessage->message)
                ]);
            }

            return [
                'conversation_id' => $conversation->id,
                'user_message' => [
                    'id' => $userMessage->id,
                    'content' => $userMessage->content,
                    'role' => $userMessage->role,
                    'created_at' => $userMessage->created_at,
                ],
                'assistant_message' => [
                    'id' => $assistantMessage->id,
                    'content' => $assistantMessage->content,
                    'role' => $assistantMessage->role,
                    'created_at' => $assistantMessage->created_at,
                    'weather_data_used' => $weatherData !== null,
                ]
            ];
        });
    }

    private function getOrCreateConversation(ChatMessageDTO $chatMessage): Conversation
    {
        if ($chatMessage->conversationId) {
            return Conversation::where('id', $chatMessage->conversationId)
                ->where('session_uuid', $chatMessage->sessionUuid)
                ->firstOrFail();
        }

        return Conversation::create([
            'session_uuid' => $chatMessage->sessionUuid,
            'title' => null,
            'last_message' => null,
            'last_message_at' => now()
        ]);
    }

    private function getWeatherDataIfNeeded(string $message): ?array
    {
        $needsWeather = $this->llmService->needsWeatherData($message);
        
        if (!$needsWeather) {
            return null;
        }

        $city = $this->llmService->extractCityFromMessage($message);
        
        if (!$city) {
            return null;
        }

        try {
            return $this->weatherService->getCurrentWeather($city);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getConversationHistory(Conversation $conversation, int $limit = 10): array
    {
        return $conversation->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content
                ];
            })
            ->toArray();
    }

    private function generateConversationTitle(string $firstMessage): string
    {
        $title = substr($firstMessage, 0, 50);
        
        if (strlen($firstMessage) > 50) {
            $title .= '...';
        }

        return $title;
    }
}