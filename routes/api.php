<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Main chat endpoints (matching frontend expectations)
Route::middleware('session.uuid')->group(function () {
    Route::post('/chat', [ChatController::class, 'sendMessage']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/conversations/{conversationId}', [ChatController::class, 'getConversation']);
    Route::delete('/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
});

// Versioned API (for future use)
Route::prefix('v1')->middleware('session.uuid')->group(function () {
    Route::post('/chat/message', [ChatController::class, 'sendMessage']);
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversations/{conversationId}', [ChatController::class, 'getConversation']);
    Route::delete('/chat/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
});

// Deprecated user endpoint - kept for backward compatibility but not used
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');