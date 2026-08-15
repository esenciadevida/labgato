<?php

namespace App\Support;

use App\Exceptions\Ai\AiContentFilterException;
use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiRateLimitException;
use App\Exceptions\Ai\AiTimeoutException;
use Throwable;

class AiErrorMessage
{
    public static function for(Throwable $e): string
    {
        return match (true) {
            $e instanceof AiTimeoutException => 'The AI took too long to respond. Please try again.',
            $e instanceof AiRateLimitException => 'You have reached your AI usage limit for today. Please try again tomorrow.',
            $e instanceof AiContentFilterException => 'The AI could not process this request. Please revise your input and try again.',
            $e instanceof AiDisabledException => 'AI features are currently disabled by the administrator.',
            default => 'The AI assistant is temporarily unavailable. Please try again later.',
        };
    }
}
