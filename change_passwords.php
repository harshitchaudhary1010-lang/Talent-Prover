<?php
/**
 * Change passwords for specific users
 */
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

// Hash the new password
$newPassword = '12345678';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update passwords
$emails = ['apple@gmail.com', 'nasa@gmail.com'];

foreach ($emails as $email) {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashedPassword, $email]);
    
    if ($result) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✓ Password updated for: {$user['name']} ({$email}) - Role: {$user['role']}<br>";
        } else {
            echo "✗ User not found: {$email}<br>";
        }
    }
}

echo "<br><strong>New password for both accounts: 12345678</strong>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Changed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #4CAF50; }
        .success { color: #4CAF50; font-weight: bold; }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Password Update Complete</h1>
        <div class="info">
            <strong>Updated passwords:</strong><br><br>
            <strong>Email:</strong> apple@gmail.com<br>
            <strong>Password:</strong> 12345678<br><br>
            <strong>Email:</strong> nasa@gmail.com<br>
            <strong>Password:</strong> 12345678<br>
        </div>
        <p>You can now login with these credentials.</p>
        <a href="/auth/login.php" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">Go to Login</a>
    </div>
</body>
</html>