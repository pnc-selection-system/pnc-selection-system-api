<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

echo "Testing user creation...\n\n";

// Test 1: Create user with plain password
$user = new User([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'role_id' => 1,
    'active' => true
]);

echo "Before save - Password attribute: " . $user->password . "\n";
echo "Is hashed? " . (str_starts_with($user->password, '$2y$') ? 'Yes' : 'No') . "\n\n";

$user->save();

echo "After save - Password hash: " . $user->password . "\n";
echo "Is hashed? " . (str_starts_with($user->password, '$2y$') ? 'Yes' : 'No') . "\n\n";

// Test 2: Verify password can be checked
echo "Testing password verification...\n";
$verified = Hash::check('password123', $user->password);
echo "Password 'password123' matches hash? " . ($verified ? 'Yes' : 'No') . "\n\n";

// Test 3: Test login with JWTAuth
echo "Testing JWT login...\n";
try {
    $token = JWTAuth::attempt(['email' => 'test@example.com', 'password' => 'password123']);
    if ($token) {
        echo "Login successful! Token: " . substr($token, 0, 20) . "...\n";
    } else {
        echo "Login failed - invalid credentials\n";
    }
} catch (Exception $e) {
    echo "Login error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";