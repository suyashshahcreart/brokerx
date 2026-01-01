<?php
function obfuscateJs(string $js): string
{
    $script = storage_path('app/obfuscate.js');
    
    // Create a temporary file to store the JS content
    $tempFile = tempnam(sys_get_temp_dir(), 'obfuscate_');
    file_put_contents($tempFile, $js);
    
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
            unlink($tempFile);
        }
    }
}
