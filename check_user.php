<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Checking admin user...\n\n";

$user = User::where('email', 'admin@gmail.com')->first();

if ($user) {
    echo "User found!\n";
    echo "Email: " . $user->email . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Password hash: " . $user->password . "\n";
    echo "Active: " . ($user->active ? 'true' : 'false') . "\n";
    echo "Role ID: " . $user->role_id . "\n";
    
    // Test password verification
    $password = 'admin123';
    if (password_verify($password, $user->password)) {
        echo "\n✓ Password 'admin123' matches the hash!\n";
    } else {
        echo "\n✗ Password 'admin123' does NOT match the hash!\n";
    }
} else {
    echo "User NOT found!\n";
}