<?php
header('Content-Type: application/json');

// Test if server is running
echo json_encode([
    'status' => 'ok',
    'message' => 'Laravel server is running!',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
]);