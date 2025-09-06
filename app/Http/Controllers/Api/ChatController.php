<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessChatMessageAction;
use App\Actions\ValidateSecurityAction;
use App\DTOs\ChatMessageDTO;
use App\Enums\WeatherErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetConversationsRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        private ProcessChatMessageAction $processChatAction,
        private ValidateSecurityAction $validateSecurityAction
    ) {}

    public function sendMessage(SendMessageRequest $request): JsonResponse
    {

        if ($this->validateSecurityAction->execute($request->message)) {
            Log::warning('🛡️ Security: Suspicious patterns detected in user message', [
                'user_id' => $request->user_id,
                'message' => substr($request->message, 0, 100) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Mensaje no válido. Por favor reformula tu consulta sobre el clima.',
                'error_type' => 'security_violation'
            ], 400);
        }

        try {
            $chatMessage = ChatMessageDTO::fromRequest($request->validated());
            $result = $this->processChatAction->execute($chatMessage);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }

    public function getConversations(GetConversationsRequest $request): JsonResponse
    {

        try {
            $user = User::findOrFail($request->user_id);
            $limit = $request->input('limit', 20);

            $conversations = $user->conversations()
                ->with(['messages' => function ($query) {
                    $query->latest()->limit(1);
                }])
                ->latest('last_message_at')
                ->limit($limit)
                ->get()
                ->map(function ($conversation) {
                    return [
                        'id' => $conversation->id,
                        'title' => $conversation->title,
                        'last_message_at' => $conversation->last_message_at,
                        'last_message' => $conversation->messages->first()?->content ?? 'Sin mensajes',
                        'messages_count' => $conversation->messages()->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);

        } catch (\Exception $e) {
            Log::error('Get conversations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las conversaciones'
            ], 500);
        }
    }

    public function getConversation(Request $request, int $conversationId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['conversation_id' => $conversationId]), [
            'user_id' => 'required|exists:users,id',
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $conversation = Conversation::with('messages')
                ->where('id', $conversationId)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'created_at' => $conversation->created_at,
                    'last_message_at' => $conversation->last_message_at,
                    'messages' => $conversation->messages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->content,
                            'role' => $message->role,
                            'created_at' => $message->created_at,
                            'weather_data_used' => isset($message->metadata['weather_data_used']),
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get conversation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada'
            ], 404);
        }
    }

    public function deleteConversation(Request $request, int $conversationId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['conversation_id' => $conversationId]), [
            'user_id' => 'required|exists:users,id',
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $conversation = Conversation::where('id', $conversationId)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            $conversation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete conversation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la conversación'
            ], 500);
        }
    }

    private function getOrCreateConversation(Request $request, User $user): Conversation
    {
        if ($request->conversation_id) {
            return Conversation::where('id', $request->conversation_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        return Conversation::create([
            'user_id' => $user->id,
            'title' => null,
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
            $weatherData = $this->weatherService->getCurrentWeather($city);
            return $weatherData;
        } catch (\Exception $e) {
            $errorMessage = $this->translateWeatherError($e->getMessage(), $city);
            Log::error('🌤️ Weather API failed for: ' . $city . ' - Error: ' . $e->getMessage() . ' - User message: ' . $errorMessage);
            
            throw new \Exception($errorMessage);
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

    private function translateWeatherError(string $errorCode, string $city): string
    {
        $weatherError = WeatherErrorCode::tryFrom($errorCode);
        
        if ($weatherError) {
            return $weatherError->getMessage($city);
        }

        return "Error desconocido del servicio meteorológico: {$errorCode}";
    }

    private function detectSuspiciousPatterns(string $message): bool
    {
        $highRiskPatterns = [
            '/ignore\s+(all\s+)?previous\s+instructions?/i',
            '/ignore\s+above/i',
            '/forget\s+(everything|all)/i',
            '/new\s+(instructions?|rules?):\s*/i',
            '/system\s*:\s*/i',
            '/assistant\s*:\s*/i',
            '/user\s*:\s*/i',
            '/role\s*:\s*(system|assistant|user)/i',
            '/act\s+as\s+(if\s+)?(you\s+are\s+)?a?/i',
            '/pretend\s+(you\s+are|to\s+be)/i',
            '/you\s+are\s+now\s+a?/i',
            '/from\s+now\s+on\s+you\s+(are|will)/i',
            '/override\s+your\s+(instructions?|role|system)/i',
            '/change\s+your\s+(role|personality|instructions?)/i',
            '/disregard\s+(your\s+)?(previous\s+)?instructions?/i',
            '/<\/?system>/i',
            '/<\/?assistant>/i',
            '/<\/?user>/i',
            '/\[SYSTEM\]/i',
            '/\[ASSISTANT\]/i',
            '/\[USER\]/i',
            '/END\s+SYSTEM/i',
            '/BEGIN\s+SYSTEM/i',
        ];

        foreach ($highRiskPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        $suspiciousScore = 0;

        if (preg_match_all('/(system|assistant|user)\s*:/i', $message) > 1) {
            $suspiciousScore += 3;
        }

        $instructionWords = ['ignore', 'forget', 'override', 'change', 'pretend', 'act', 'role', 'system'];
        foreach ($instructionWords as $word) {
            if (stripos($message, $word) !== false) {
                $suspiciousScore += 1;
            }
        }

        if (strlen($message) > 500 && substr_count($message, '.') > 5) {
            $suspiciousScore += 2;
        }

        return $suspiciousScore >= 4;
    }
}