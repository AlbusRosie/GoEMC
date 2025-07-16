<?php
/**
 * Helper functions for the application
 */

/**
 * Safe logging function
 * @param string $filename Log filename
 * @param string $message Message to log
 * @param string $logDir Log directory (default: logs)
 */
function safeLog($filename, $message, $logDir = 'logs') {
    try {
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . $filename;
        file_put_contents($logFile, $message, FILE_APPEND);
    } catch (Exception $e) {
        // Silently fail if logging fails
        error_log("Logging failed: " . $e->getMessage());
    }
}

/**
 * Debug logging function
 * @param string $message Message to log
 * @param string $type Log type (default: cart_debug)
 */
function debugLog($message, $type = 'cart_debug') {
    safeLog($type . '.log', $message . "\n");
}

/**
 * Router debug logging function
 * @param string $message Message to log
 */
function routerLog($message) {
    safeLog('router_debug.log', $message . "\n");
}
?> 