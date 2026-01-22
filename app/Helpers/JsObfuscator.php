<?php
function obfuscateJs(string $js): string
{
    $script = storage_path('app/obfuscate.js');
    
    // Use Laravel's storage directory instead of system temp directory
    // This ensures proper permissions and avoids PHP 8.3 tempnam() issues
    $tempDir = storage_path('app/temp');
    
    // Create temp directory if it doesn't exist
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0755, true);
    }
    
    // Create a temporary file in Laravel's storage directory
    $tempFile = $tempDir . '/obfuscate_' . uniqid() . '_' . time() . '.js';
    
    // Write JS content to temp file
    if (file_put_contents($tempFile, $js) === false) {
        throw new Exception('Failed to create temporary file for JS obfuscation');
    }
    
    try {
        $cmd = 'node ' . escapeshellarg($script) . ' ' . escapeshellarg($tempFile) . ' 2>&1';
        exec($cmd, $output, $code);

        if ($code !== 0) {
            $errorMsg = implode("\n", $output);
            throw new Exception('JS obfuscation failed: ' . $errorMsg);
        }

        return implode("\n", $output);
    } finally {
        // Clean up temporary file
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}
