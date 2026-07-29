<?php

// Simulate frontend login request
$url = 'http://localhost:8000/api/auth/login';

$data = [
    'email' => 'admin@gmail.com',
    'password' => 'admin123'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
    ],
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error making request\n";
} else {
    echo "Response:\n";
    echo $result . "\n";
}