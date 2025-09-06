<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;

class ValidateSecurityAction
{
    private array $highRiskPatterns = [
        '/ignore\\s+(all\\s+)?previous\\s+instructions?/i',
        '/ignore\\s+above/i',
        '/forget\\s+(everything|all)/i',
        '/new\\s+(instructions?|rules?):\\s*/i',
        '/system\\s*:\\s*/i',
        '/assistant\\s*:\\s*/i',
        '/user\\s*:\\s*/i',
        '/role\\s*:\\s*(system|assistant|user)/i',
        '/act\\s+as\\s+(if\\s+)?(you\\s+are\\s+)?a?/i',
        '/pretend\\s+(you\\s+are|to\\s+be)/i',
        '/you\\s+are\\s+now\\s+a?/i',
        '/from\\s+now\\s+on\\s+you\\s+(are|will)/i',
        '/override\\s+your\\s+(instructions?|role|system)/i',
        '/change\\s+your\\s+(role|personality|instructions?)/i',
        '/disregard\\s+(your\\s+)?(previous\\s+)?instructions?/i',
        '/<\\/?system>/i',
        '/<\\/?assistant>/i',
        '/<\\/?user>/i',
        '/\\[SYSTEM\\]/i',
        '/\\[ASSISTANT\\]/i',
        '/\\[USER\\]/i',
        '/END\\s+SYSTEM/i',
        '/BEGIN\\s+SYSTEM/i',
    ];

    private array $instructionWords = [
        'ignore', 'forget', 'override', 'change', 
        'pretend', 'act', 'role', 'system'
    ];

    public function execute(string $message): bool
    {
        foreach ($this->highRiskPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        $suspiciousScore = 0;

        if (preg_match_all('/(system|assistant|user)\\s*:/i', $message) > 1) {
            $suspiciousScore += 3;
        }

        foreach ($this->instructionWords as $word) {
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