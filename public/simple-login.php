<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Login Test</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 24px;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus, select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        .btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s;
            margin-top: 6px;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }
        .result {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.6;
        }
        .result.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .result.error { background: #fbe9e7; color: #c62828; border: 1px solid #ffccbc; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
        .token-box {
            background: #f5f5f5;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-family: monospace;
            word-break: break-all;
            margin-top: 10px;
        }
        .divider {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>🔐 Simple Login Test</h1>
    <p class="subtitle">Server-side login — no JavaScript issues!</p>

    <?php
    // ─── Handle form submission ───────────────────────────────
    $message = '';
    $messageType = '';
    $token = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Bootstrap Laravel
        require __DIR__ . '/../vendor/autoload.php';
        $app = require __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $action = $_POST['action'] ?? '';

        if ($action === 'register') {
            // ── REGISTER ──────────────────────────────────────
            $name     = $_POST['reg_name'] ?? '';
            $email    = $_POST['reg_email'] ?? '';
            $password = $_POST['reg_password'] ?? '';
            $role_id  = $_POST['reg_role_id'] ?? '1';

            try {
                $user = \App\Models\User::create([
                    'role_id'  => $role_id,
                    'name'     => $name,
                    'email'    => $email,
                    'password' => $password,
                    'active'   => true,
                ]);
                $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
                $message = "✅ User <strong>" . htmlspecialchars($name) . "</strong> created successfully!<br>
                           Email: " . htmlspecialchars($email) . "<br>
                           Password: " . htmlspecialchars($password) . "<br>
                           <span style='color:#666;font-size:12px;'>You can now login below.</span>";
                $messageType = 'success';
            } catch (\Exception $e) {
                $message = "❌ Registration failed: " . htmlspecialchars($e->getMessage());
                $messageType = 'error';
            }
        } elseif ($action === 'login') {
            // ── LOGIN ─────────────────────────────────────────
            $email    = $_POST['login_email'] ?? '';
            $password = $_POST['login_password'] ?? '';

            try {
                if ($token = \Tymon\JWTAuth\Facades\JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $message = "✅ <strong>Login Successful!</strong><br>
                               👤 " . htmlspecialchars($user->name) . "<br>
                               📧 " . htmlspecialchars($user->email) . "<br>
                               🆔 User ID: " . $user->id . "<br>
                               🏷️ Role ID: " . ($user->role_id ?? 'N/A');
                    $messageType = 'success';
                } else {
                    // Check if user exists
                    $existingUser = \App\Models\User::where('email', $email)->first();
                    if (!$existingUser) {
                        $message = "❌ User <strong>" . htmlspecialchars($email) . "</strong> not found. Please register first.";
                    } else {
                        $message = "❌ Wrong password for <strong>" . htmlspecialchars($email) . "</strong>.<br>
                                   ℹ️ The account exists but the password doesn't match.";
                    }
                    $messageType = 'error';
                }
            } catch (\Exception $e) {
                $message = "❌ Error: " . htmlspecialchars($e->getMessage());
                $messageType = 'error';
            }
        }
    }

    // Show message if any
    if ($message) {
        echo '<div class="result ' . $messageType . '">' . $message . '</div>';
        if ($token) {
            echo '<div class="token-box">Token: ' . htmlspecialchars($token) . '</div>';
        }
    }
    ?>

    <!-- ─── REGISTER FORM ─────────────────────────────────── -->
    <h2 style="font-size:18px;margin-bottom:16px;color:#333;">📝 Register New User</h2>
    <form method="POST">
        <input type="hidden" name="action" value="register">

        <div class="form-group">
            <label for="reg_name">Name</label>
            <input type="text" id="reg_name" name="reg_name" value="Keang" required>
        </div>
        <div class="form-group">
            <label for="reg_email">Email</label>
            <input type="email" id="reg_email" name="reg_email" value="keang@gmail.com" required>
        </div>
        <div class="form-group">
            <label for="reg_password">Password</label>
            <input type="text" id="reg_password" name="reg_password" value="mypass123" required>
        </div>
        <div class="form-group">
            <label for="reg_role_id">Role</label>
            <select id="reg_role_id" name="reg_role_id">
                <option value="1">Admin</option>
                <option value="2">Manager</option>
                <option value="3">Officer</option>
            </select>
        </div>
        <button type="submit" class="btn">✨ Create User</button>
    </form>

    <hr class="divider">

    <!-- ─── LOGIN FORM ─────────────────────────────────────── -->
    <h2 style="font-size:18px;margin-bottom:16px;color:#333;">🔑 Login</h2>
    <form method="POST">
        <input type="hidden" name="action" value="login">

        <div class="form-group">
            <label for="login_email">Email</label>
            <input type="email" id="login_email" name="login_email" value="keang@gmail.com" required>
        </div>
        <div class="form-group">
            <label for="login_password">Password</label>
            <input type="text" id="login_password" name="login_password" value="mypass123" required>
        </div>
        <button type="submit" class="btn">🔑 Login</button>
    </form>

    <hr class="divider">

    <!-- ─── QUICK TEST BUTTONS ─────────────────────────────── -->
    <h2 style="font-size:18px;margin-bottom:16px;color:#333;">⚡ Quick Test</h2>
    <p style="font-size:13px;color:#666;margin-bottom:12px;">
        Click to test with pre-set accounts:
    </p>

    <form method="POST" style="margin-bottom:8px;">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="login_email" value="admin@gmail.com">
        <input type="hidden" name="login_password" value="admin123">
        <button type="submit" class="btn btn-secondary" style="background:#4361ee;">👑 Login as Admin</button>
    </form>

    <form method="POST" style="margin-bottom:8px;">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="login_email" value="manager@gmail.com">
        <input type="hidden" name="login_password" value="manager123">
        <button type="submit" class="btn btn-secondary" style="background:#6f42c1;">👔 Login as Manager</button>
    </form>

    <form method="POST">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="login_email" value="officer@gmail.com">
        <input type="hidden" name="login_password" value="officer123">
        <button type="submit" class="btn btn-secondary" style="background:#28a745;">🛡️ Login as Officer</button>
    </form>
</div>
</body>
</html>
