<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'session_uuid',
        'title',
        'last_message',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function updateLastMessage(string $content): void
    {
        $this->update([
            'last_message' => $content,
            'last_message_at' => now()
        ]);
    }

    public static function forSession(string $sessionUuid)
    {
        return static::where('session_uuid', $sessionUuid);
    }
}
