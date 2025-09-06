<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessChatMessageAction;
use App\Actions\ValidateSecurityAction;
use App\DTOs\ChatMessageDTO;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(
        private ProcessChatMessageAction $processChatAction,
        private ValidateSecurityAction $validateSecurityAction
    ) {}

    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|exists:conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        // Security validation
        if ($this->validateSecurityAction->execute($request->message)) {
            Log::warning('Security: Suspicious patterns detected', [
                'session_uuid' => $request->session_uuid,
                'message_preview' => substr($request->message, 0, 100) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Mensaje no válido. Por favor reformula tu consulta sobre el clima.',
                'error_type' => 'security_violation'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $sessionUuid = $request->session_uuid;
            
            // Get or create conversation
            $conversation = $this->getOrCreateConversation($request->conversation_id, $sessionUuid);

            // Create DTO and process message
            $chatMessage = new ChatMessageDTO(
                message: $request->message,
                conversationId: $conversation->id,
                sessionUuid: $sessionUuid
            );

            $result = $this->processChatAction->execute($chatMessage);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Chat error: ' . $e->getMessage(), [
                'session_uuid' => $request->session_uuid,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.',
                'error_type' => 'llm_error'
            ], 500);
        }
    }

    public function getConversations(Request $request): JsonResponse
    {
        try {
            $sessionUuid = $request->session_uuid;
            $limit = $request->input('limit', 20);

            $conversations = Conversation::forSession($sessionUuid)
                ->latest('last_message_at')
                ->limit($limit)
                ->get()
                ->map(function ($conversation) {
                    return [
                        'id' => $conversation->id,
                        'title' => $conversation->title ?: 'Nueva conversación',
                        'last_message_at' => $conversation->last_message_at,
                        'last_message' => $conversation->last_message ?: 'Sin mensajes',
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
        $validator = Validator::make(['conversation_id' => $conversationId], [
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        try {
            $sessionUuid = $request->session_uuid;
            
            $conversation = Conversation::with('messages')
                ->where('id', $conversationId)
                ->where('session_uuid', $sessionUuid)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?: 'Nueva conversación',
                    'created_at' => $conversation->created_at,
                    'last_message_at' => $conversation->last_message_at,
                    'messages' => $conversation->messages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->content,
                            'role' => $message->role,
                            'created_at' => $message->created_at,
                            'weather_data_used' => $message->weather_data_used ?? false,
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
        $validator = Validator::make(['conversation_id' => $conversationId], [
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada'
            ], 404);
        }

        try {
            $sessionUuid = $request->session_uuid;
            
            $conversation = Conversation::where('id', $conversationId)
                ->where('session_uuid', $sessionUuid)
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

    private function getOrCreateConversation(?int $conversationId, string $sessionUuid): Conversation
    {
        if ($conversationId) {
            return Conversation::where('id', $conversationId)
                ->where('session_uuid', $sessionUuid)
                ->firstOrFail();
        }

        return Conversation::create([
            'session_uuid' => $sessionUuid,
            'title' => null,
            'last_message' => null,
            'last_message_at' => now()
        ]);
    }
}