<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

echo "Testing JWT Login...\n\n";

// Test credentials
$credentials = [
    'email' => 'admin@gmail.com',
    'password' => 'admin123'
];

echo "Attempting login with:\n";
echo "Email: {$credentials['email']}\n";
echo "Password: {$credentials['password']}\n\n";

try {
    // Attempt to login
    if (!$token = JWTAuth::attempt($credentials)) {
        echo "✗ Login FAILED - Invalid credentials\n";
        
        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            echo "  Reason: User not found in database\n";
        } else {
            echo "  User found, checking password...\n";
            if (!password_verify($credentials['password'], $user->password)) {
                echo "  Reason: Password does not match\n";
            } else {
                echo "  Password matches, but JWT::attempt failed\n";
                echo "  This might be a JWT configuration issue\n";
            }
        }
    } else {
        echo "✓ Login SUCCESSFUL!\n";
        echo "Token: " . substr($token, 0, 50) . "...\n";
        
        // Get user info
        $user = Auth::user();
        echo "\nUser Info:\n";
        echo "ID: " . $user->id . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Role ID: " . $user->role_id . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Exception occurred:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}