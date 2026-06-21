<?php
/**
 * Check and Fix NASA Password
 */
require_once __DIR__ . '/config/db.php';

echo "<h2>Checking Users...</h2>\n";

$pdo = getDB();

// First, let's see all users
echo "<h3>All Users in Database:</h3>\n";
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id");
$users = $stmt->fetchAll();

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>\n";
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
    echo "</tr>\n";
}
echo "</table><br>\n";

// Now fix the nasa@gmail.com password
echo "<h3>Fixing nasa@gmail.com password...</h3>\n";

$newPassword = '12345678';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "New password: <strong>$newPassword</strong><br>\n";
echo "Hashed: <code style='font-size: 10px;'>" . htmlspecialchars($hashedPassword) . "</code><br><br>\n";

// Update the password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$hashedPassword, 'nasa@gmail.com']);

if ($result && $stmt->rowCount() > 0) {
    echo "✅ <strong style='color: green;'>Password updated successfully!</strong><br>\n";
    
    // Verify it works
    $stmt = $pdo->prepare("SELECT id, name, email, role, password FROM users WHERE email = ?");
    $stmt->execute(['nasa@gmail.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<br>User found:<br>\n";
        echo "ID: " . $user['id'] . "<br>\n";
        echo "Name: " . htmlspecialchars($user['name']) . "<br>\n";
        echo "Email: " . htmlspecialchars($user['email']) . "<br>\n";
        echo "Role: " . htmlspecialchars($user['role']) . "<br>\n";
        
        // Test the password
        if (password_verify('12345678', $user['password'])) {
            echo "<br>✅ <strong style='color: green;'>Password verification: SUCCESS!</strong><br>\n";
            echo "You can now login with:<br>\n";
            echo "Email: <strong>nasa@gmail.com</strong><br>\n";
            echo "Password: <strong>12345678</strong><br>\n";
        } else {
            echo "<br>❌ <strong style='color: red;'>Password verification: FAILED!</strong><br>\n";
        }
    }
} else {
    echo "❌ <strong style='color: red;'>Failed to update password or user not found</strong><br>\n";
}

// Also update apple@gmail.com if it exists
echo "<br><hr><h3>Checking apple@gmail.com...</h3>\n";
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['apple@gmail.com']);
if ($stmt->fetch()) {
    echo "User found! Updating password...<br>\n";
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, 'apple@gmail.com']);
    echo "✅ Password updated for apple@gmail.com<br>\n";
} else {
    echo "❌ User apple@gmail.com does not exist in database<br>\n";
}

echo "<br><br><a href='/auth/login.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Test Login Now</a>\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Fixed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 { color: #333; }
        h3 { color: #555; margin-top: 20px; }
        table { background: white; width: 100%; margin: 10px 0; }
        th { background: #4CAF50; color: white; }
    </style>
</head>
<body>
</body>
</html>