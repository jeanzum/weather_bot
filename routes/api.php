<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/chat/message', [ChatController::class, 'sendMessage']);
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversations/{conversationId}', [ChatController::class, 'getConversation']);
    Route::delete('/chat/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');