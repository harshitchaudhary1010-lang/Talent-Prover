<?php
/**
 * Debug NASA Login - See exactly what's happening
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$debug = [];
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    $debug[] = "📧 Email entered: '" . htmlspecialchars($email) . "'";
    $debug[] = "🔑 Password entered: '" . htmlspecialchars($password) . "' (length: " . strlen($password) . ")";
    $debug[] = "📏 Email after strtolower+trim: '" . htmlspecialchars($email) . "'";
    
    // Check regex validation
    if (preg_match('/^[a-z0-9._%+\-]+@gmail\.com$/', $email)) {
        $debug[] = "✅ Email passed regex validation";
    } else {
        $debug[] = "❌ Email FAILED regex validation";
    }
    
    if (!$email || !$password) {
        $debug[] = "❌ Empty email or password";
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $debug[] = "✅ User found in database";
            $debug[] = "   - ID: " . $user['id'];
            $debug[] = "   - Name: " . htmlspecialchars($user['name']);
            $debug[] = "   - Email: " . htmlspecialchars($user['email']);
            $debug[] = "   - Role: " . htmlspecialchars($user['role']);
            $debug[] = "   - Status: " . htmlspecialchars($user['status']);
            $debug[] = "   - Password Hash: " . substr($user['password'], 0, 20) . "...";
            
            // Test password
            if (password_verify($password, $user['password'])) {
                $debug[] = "✅ Password verification: SUCCESS!";
                
                if ($user['status'] === 'blocked') {
                    $debug[] = "❌ Account is BLOCKED";
                } elseif ($user['status'] === 'pending') {
                    $debug[] = "❌ Account is PENDING approval";
                } else {
                    $debug[] = "✅ Account status is: " . $user['status'];
                    $debug[] = "🎉 LOGIN SUCCESSFUL!";
                    
                    // Actually log in
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $loginSuccess = true;
                    
                    $debug[] = "🔄 Redirecting to /Dashboard/" . $user['role'] . ".php";
                }
            } else {
                $debug[] = "❌ Password verification: FAILED!";
                $debug[] = "   The password you entered doesn't match the stored hash";
                
                // Test with known password
                if (password_verify('12345678', $user['password'])) {
                    $debug[] = "   ℹ️ Note: Password '12345678' WOULD work for this account";
                }
            }
        } else {
            $debug[] = "❌ User NOT found in database with email: " . htmlspecialchars($email);
            
            // Check if similar email exists
            $stmt = $pdo->prepare("SELECT email FROM users WHERE email LIKE ?");
            $stmt->execute(['%nasa%']);
            $similar = $stmt->fetchAll();
            if ($similar) {
                $debug[] = "   Similar emails found:";
                foreach ($similar as $s) {
                    $debug[] = "   - " . htmlspecialchars($s['email']);
                }
            }
        }
    }
}

if ($loginSuccess) {
    header('Location: /Dashboard/company.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug NASA Login</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #1a1a1a;
            color: #0f0;
        }
        .container {
            background: #000;
            padding: 30px;
            border: 2px solid #0f0;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,255,0,0.3);
        }
        h1 {
            color: #0ff;
            text-align: center;
            text-shadow: 0 0 10px #0ff;
        }
        .form-box {
            background: #1a1a1a;
            padding: 20px;
            border: 1px solid #0f0;
            border-radius: 8px;
            margin: 20px 0;
        }
        input {
            width: 100%;
            padding: 12px;
            background: #000;
            border: 1px solid #0f0;
            color: #0f0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 0;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #0f0;
            color: #000;
            border: none;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        button:hover {
            background: #0ff;
            box-shadow: 0 0 10px #0ff;
        }
        .debug {
            background: #1a1a1a;
            padding: 20px;
            border: 1px solid #ff0;
            border-radius: 8px;
            margin: 20px 0;
        }
        .debug h2 {
            color: #ff0;
            margin-bottom: 15px;
        }
        .debug-line {
            margin: 8px 0;
            padding: 5px;
            border-left: 3px solid #0f0;
            padding-left: 10px;
        }
        .debug-line.error {
            border-left-color: #f00;
            color: #f00;
        }
        .debug-line.success {
            border-left-color: #0f0;
            color: #0f0;
        }
        .debug-line.info {
            border-left-color: #0ff;
            color: #0ff;
        }
        .preset {
            background: #333;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 4px;
            margin: 10px 0;
            cursor: pointer;
        }
        .preset:hover {
            background: #444;
        }
        a {
            color: #0ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 NASA LOGIN DEBUGGER</h1>
        
        <div class="form-box">
            <form method="POST">
                <label>Email:</label>
                <input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'nasa@gmail.com') ?>" placeholder="nasa@gmail.com">
                
                <label>Password:</label>
                <input type="text" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" placeholder="12345678">
                
                <button type="submit">🚀 TEST LOGIN</button>
            </form>
            
            <div class="preset" onclick="fillForm('nasa@gmail.com', '12345678')">
                📋 Quick Fill: nasa@gmail.com / 12345678
            </div>
        </div>

        <?php if (!empty($debug)): ?>
        <div class="debug">
            <h2>📊 DEBUG OUTPUT:</h2>
            <?php foreach ($debug as $line): ?>
                <?php 
                $class = 'info';
                if (strpos($line, '❌') !== false || strpos($line, 'FAILED') !== false) {
                    $class = 'error';
                } elseif (strpos($line, '✅') !== false || strpos($line, 'SUCCESS') !== false) {
                    $class = 'success';
                }
                ?>
                <div class="debug-line <?= $class ?>">
                    <?= htmlspecialchars($line) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center; border-top: 1px solid #0f0; padding-top: 20px;">
            <a href="/auth/login.php">🔗 Go to Normal Login</a> |
            <a href="/test_login_nasa.php">🔗 Simple Test Login</a> |
            <a href="/list_users.php">🔗 View All Users</a>
        </div>
    </div>

    <script>
        function fillForm(email, password) {
            document.querySelector('input[name=email]').value = email;
            document.querySelector('input[name=password]').value = password;
        }
    </script>
</body>
</html>
