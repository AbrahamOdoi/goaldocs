<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function log($message, $userId = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $controller = isset($backtrace[2]['class']) ? class_basename($backtrace[2]['class']) : 'N/A';
        $function = isset($backtrace[2]['function']) ? $backtrace[2]['function'] : 'N/A';

        $timestamp = now()->format('Y-m-d H:i:s');
        $userId = $userId ?? auth()->id() ?? 'N/A';

        $logMessage = "[$timestamp][$controller][$function][$userId][$message]";


        Log::channel('audit')->info($logMessage);
    }
}
