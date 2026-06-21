<?php
/**
 * Test NASA Login Directly
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$testResult = '';
$testColor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = 'nasa@gmail.com';
    $password = $_POST['password'] ?? '';
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $testResult = "User found: " . htmlspecialchars($user['name']) . " (" . $user['role'] . ")\n";
        
        if (password_verify($password, $user['password'])) {
            $testResult .= "✅ Password is CORRECT!\n";
            $testResult .= "Logging you in...\n";
            $testColor = 'green';
            
            // Actually log in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: /Dashboard/company.php');
            exit;
        } else {
            $testResult .= "❌ Password is INCORRECT!\n";
            $testResult .= "You entered: '" . htmlspecialchars($password) . "'\n";
            $testResult .= "Expected: 12345678\n";
            $testColor = 'red';
        }
    } else {
        $testResult = "❌ User not found!";
        $testColor = 'red';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test NASA Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
            border-radius: 4px;
        }
        .result {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            white-space: pre-wrap;
            font-family: monospace;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin: 10px 0;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #45a049;
        }
        .success { color: green; }
        .error { color: red; }
        .preset-btn {
            background: #2196F3;
            margin-top: 5px;
            padding: 10px;
            font-size: 14px;
        }
        .preset-btn:hover {
            background: #1976D2;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔑 Test NASA Login</h1>
        
        <div class="info">
            <strong>Account Details:</strong><br>
            Email: <code>nasa@gmail.com</code><br>
            Password: <code>12345678</code>
        </div>

        <?php if ($testResult): ?>
            <div class="result" style="border-left: 4px solid <?= $testColor ?>; color: <?= $testColor ?>;">
                <?= $testResult ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label><strong>Enter Password:</strong></label>
            <input type="text" name="password" placeholder="Type password here..." value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" autofocus>
            
            <button type="submit">🔓 Test Login</button>
            <button type="button" class="preset-btn" onclick="document.querySelector('input[name=password]').value='12345678'">
                Fill with: 12345678
            </button>
        </form>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <strong>Other Options:</strong><br><br>
            <a href="/auth/login.php" style="color: #2196F3;">Go to Normal Login Page</a><br>
            <a href="/check_and_fix_password.php" style="color: #2196F3;">View Password Fix Details</a><br>
            <a href="/list_users.php" style="color: #2196F3;">View All Users</a>
        </div>
    </div>

    <script>
        // Auto-submit if password is pre-filled
        window.onload = function() {
            const input = document.querySelector('input[name=password]');
            input.focus();
            input.select();
        }
    </script>
</body>
</html>
