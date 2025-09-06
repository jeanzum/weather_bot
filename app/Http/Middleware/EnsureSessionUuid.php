<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionUuid
{
    /**
     * Handle an incoming request.
     *
     * Ensures each session has a unique UUID for conversation management.
     * This provides better security than user_id based system.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get session UUID from header (sent by frontend) or fallback to Laravel session
        $sessionUuid = $request->header('X-Chat-Session-UUID');
        
        if (!$sessionUuid) {
            // Fallback to Laravel session for backward compatibility
            if (!session()->has('chat_session_uuid')) {
                session()->put('chat_session_uuid', Str::uuid()->toString());
            }
            $sessionUuid = session('chat_session_uuid');
        }

        // Make the session UUID available in the request for controllers
        $request->merge([
            'session_uuid' => $sessionUuid
        ]);

        return $next($request);
    }
}
