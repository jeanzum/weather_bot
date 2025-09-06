<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'session_uuid',
        'content',
        'role',
        'weather_data_used'
    ];

    protected $casts = [
        'weather_data_used' => 'boolean'
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isFromUser(): bool
    {
        return $this->role === 'user';
    }

    public function isFromAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public static function forSession(string $sessionUuid)
    {
        return static::where('session_uuid', $sessionUuid);
    }
}
