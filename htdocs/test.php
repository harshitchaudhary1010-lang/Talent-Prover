<?php
/**
 * Quick Test Page - Verify PHP and Basic Setup
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentProve - Quick Test</title>
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
        .success {
            color: #4CAF50;
            font-weight: bold;
            font-size: 24px;
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
        a {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
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
        <h1>✓ PHP is Working!</h1>
        <p class="success">Your server is correctly processing PHP files.</p>
        
        <div class="info">
            <strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?><br>
            <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
            <strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
        </div>
    </div>

    <div class="card">
        <h2>📍 Current Request Information</h2>
        <table>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Request URI</td>
                <td><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?></td>
            </tr>
            <tr>
                <td>Script Name</td>
                <td><?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '') ?></td>
            </tr>
            <tr>
                <td>Server Name</td>
                <td><?= htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'Unknown') ?></td>
            </tr>
            <tr>
                <td>Server Port</td>
                <td><?= htmlspecialchars($_SERVER['SERVER_PORT'] ?? 'Unknown') ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '') ?></td>
            </tr>
            <tr>
                <td>File Path</td>
                <td><?= htmlspecialchars(__FILE__) ?></td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🔗 Test Links</h2>
        <p>Click these links to verify routing is working:</p>
        
        <a href="/" class="btn">Homepage (index.php)</a>
        <a href="/check.php" class="btn">System Check</a>
        <a href="/auth/login.php" class="btn">Login Page</a>
        <a href="/auth/register.php" class="btn">Register Page</a>
        <a href="/tasks.php" class="btn">Tasks Page</a>
        
        <div class="info" style="margin-top: 20px;">
            <strong>If links above show 404 errors:</strong><br>
            1. Make sure .htaccess file exists in root directory<br>
            2. Check that Apache's mod_rewrite is enabled<br>
            3. Try accessing with full .php extension<br>
            4. Run the <a href="/check.php">System Check</a> for detailed diagnostics
        </div>
    </div>

    <div class="card">
        <h2>🗂️ File Structure Check</h2>
        <table>
            <tr>
                <th>File/Directory</th>
                <th>Status</th>
            </tr>
            <?php
            $files = [
                '.htaccess' => '.htaccess',
                'index.php' => 'index.php',
                'config/db.php' => 'config/db.php',
                'config/session.php' => 'config/session.php',
                'auth/login.php' => 'auth/login.php',
                'auth/register.php' => 'auth/register.php',
                'Dashboard/' => 'Dashboard/',
                'storage/sessions/' => 'storage/sessions/',
            ];
            foreach ($files as $label => $path) {
                $fullPath = __DIR__ . '/' . $path;
                $exists = (is_file($fullPath) || is_dir($fullPath));
                $status = $exists ? '✓ Exists' : '✗ Missing';
                $color = $exists ? 'green' : 'red';
                echo "<tr><td>$label</td><td style='color:$color;font-weight:bold;'>$status</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="card">
        <h2>🔌 Database Test</h2>
        <?php
        try {
            require_once __DIR__ . '/config/db.php';
            $testDsn = 'mysql:host=' . DB_HOST;
            $testPdo = new PDO($testDsn, DB_USER, DB_PASS);
            echo '<p style="color: green; font-weight: bold;">✓ Database connection successful!</p>';
            echo '<div class="info">';
            echo '<strong>Host:</strong> ' . DB_HOST . '<br>';
            echo '<strong>Database:</strong> ' . DB_NAME . '<br>';
            echo '<strong>User:</strong> ' . DB_USER;
            echo '</div>';
            
            // Check if database exists
            $stmt = $testPdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
            if ($stmt->fetch()) {
                echo '<p style="color: green;">✓ Database "' . DB_NAME . '" exists</p>';
            } else {
                echo '<p style="color: orange;">⚠ Database "' . DB_NAME . '" does not exist yet (will be created on first visit)</p>';
            }
        } catch (PDOException $e) {
            echo '<p style="color: red; font-weight: bold;">✗ Database connection failed!</p>';
            echo '<div class="info" style="background: #ffebee; border-color: #f44336;">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br><br>';
            echo '<strong>Solution:</strong> Make sure MySQL is running in XAMPP Control Panel';
            echo '</div>';
        } catch (Exception $e) {
            echo '<p style="color: red; font-weight: bold;">✗ Configuration error!</p>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>📚 Next Steps</h2>
        <ol>
            <li>Run the <a href="/check.php">full system check</a> for detailed diagnostics</li>
            <li>If everything looks good, visit the <a href="/">homepage</a></li>
            <li>Create an account at <a href="/auth/register.php">registration page</a></li>
            <li>Read the <a href="/SETUP_INSTRUCTIONS.md">setup instructions</a> if you encounter issues</li>
        </ol>
    </div>

    <div style="text-align: center; color: #999; padding: 20px;">
        TalentProve - Task-Based Hiring Platform
    </div>
</body>
</html>
