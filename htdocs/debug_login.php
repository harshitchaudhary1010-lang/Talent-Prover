<?php
/**
 * Debug Login Session - Check what's happening after login
 */
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - TalentProve</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #4CAF50;
            color: white;
        }
        pre {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Login Session Debug</h1>
        
        <?php if (isLoggedIn()): ?>
            <p class="success">✓ You are logged in!</p>
            
            <h2>Session Information</h2>
            <table>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>User ID</td>
                    <td><?= htmlspecialchars($_SESSION['user_id'] ?? 'Not set') ?></td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><?= htmlspecialchars($_SESSION['name'] ?? 'Not set') ?></td>
                </tr>
                <tr>
                    <td>Role</td>
                    <td><?= htmlspecialchars($_SESSION['role'] ?? 'Not set') ?></td>
                </tr>
                <tr>
                    <td>Session ID</td>
                    <td><?= htmlspecialchars(session_id()) ?></td>
                </tr>
            </table>

            <?php
            // Check if user exists in database
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user): ?>
                    <h2>Database User Record</h2>
                    <table>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                        <tr>
                            <td>ID</td>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                        </tr>
                        <tr>
                            <td>Name</td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td>Role</td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td><?= htmlspecialchars($user['status']) ?></td>
                        </tr>
                    </table>

                    <?php if ($user['role'] === 'student'): ?>
                        <h2>Student Profile</h2>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $profile = $stmt->fetch();
                        
                        if ($profile): ?>
                            <table>
                                <tr>
                                    <th>Field</th>
                                    <th>Value</th>
                                </tr>
                                <tr>
                                    <td>Skills</td>
                                    <td><?= htmlspecialchars($profile['skills'] ?? 'Not set') ?></td>
                                </tr>
                                <tr>
                                    <td>Bio</td>
                                    <td><?= htmlspecialchars($profile['bio'] ?? 'Not set') ?></td>
                                </tr>
                                <tr>
                                    <td>Portfolio</td>
                                    <td><?= htmlspecialchars($profile['portfolio_link'] ?? 'Not set') ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="info">
                                <strong>⚠️ No student profile found!</strong><br>
                                The student_profiles record is missing for this user.
                                This might be created when you edit your profile.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <h2>Redirect Test</h2>
                    <div class="info">
                        <strong>Expected redirect URL:</strong><br>
                        <code>/Dashboard/<?= htmlspecialchars($user['role']) ?>.php</code>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="/Dashboard/<?= htmlspecialchars($user['role']) ?>.php" class="btn">
                            Go to <?= htmlspecialchars(ucfirst($user['role'])) ?> Dashboard
                        </a>
                        <a href="/auth/logout.php" class="btn" style="background: #f44336;">Logout</a>
                    </div>

                <?php else: ?>
                    <div class="info" style="background: #ffebee; border-color: #f44336;">
                        <strong class="error">✗ User not found in database!</strong><br>
                        Session says you're logged in, but user doesn't exist in database.
                    </div>
                <?php endif;

            } catch (Exception $e) {
                echo '<div class="info" style="background: #ffebee; border-color: #f44336;">';
                echo '<strong class="error">Database Error:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            ?>

        <?php else: ?>
            <p class="error">✗ You are not logged in</p>
            <div class="info">
                <strong>What to do:</strong><br>
                1. Go to <a href="/auth/login.php">login page</a><br>
                2. Login with your credentials<br>
                3. Come back to this page to see session info
            </div>
            <a href="/auth/login.php" class="btn">Go to Login</a>
        <?php endif; ?>

        <h2>Full Session Data</h2>
        <pre><?php print_r($_SESSION); ?></pre>

        <h2>Server Information</h2>
        <pre><?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
        ?></pre>
    </div>
</body>
</html>
