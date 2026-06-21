<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentProve - System Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #1a202c;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .check-item {
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            border-left: 4px solid;
            background: #f7fafc;
        }
        .check-item.success {
            border-color: #48bb78;
            background: #f0fff4;
        }
        .check-item.error {
            border-color: #f56565;
            background: #fff5f5;
        }
        .check-item.warning {
            border-color: #ed8936;
            background: #fffaf0;
        }
        .check-title {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check-desc {
            color: #4a5568;
            font-size: 14px;
            line-height: 1.6;
        }
        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            color: white;
            font-weight: bold;
        }
        .icon.success { background: #48bb78; }
        .icon.error { background: #f56565; }
        .icon.warning { background: #ed8936; }
        .code {
            background: #2d3748;
            color: #68d391;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: "Courier New", monospace;
            font-size: 13px;
        }
        .summary {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            text-align: center;
        }
        .summary h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        .summary p {
            opacity: 0.9;
            line-height: 1.6;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        pre {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 TalentProve System Check</h1>
        <p class="subtitle">Verifying your installation and configuration</p>

        <?php
        $checks = [];
        $errors = 0;
        $warnings = 0;
        $success = 0;

        // Check 1: PHP Version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            $checks[] = [
                'type' => 'success',
                'title' => 'PHP Version',
                'desc' => "PHP <span class='code'>$phpVersion</span> is installed and compatible. ✓"
            ];
            $success++;
        } else {
            $checks[] = [
                'type' => 'error',
                'title' => 'PHP Version',
                'desc' => "PHP <span class='code'>$phpVersion</span> is too old. Requires PHP 7.4 or higher."
            ];
            $errors++;
        }

        // Check 2: Required Extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        if (empty($missingExtensions)) {
            $checks[] = [
                'type' => 'success',
                'title' => 'PHP Extensions',
                'desc' => 'All required PHP extensions are loaded. ✓'
            ];
            $success++;
        } else {
            $checks[] = [
                'type' => 'error',
                'title' => 'PHP Extensions',
                'desc' => 'Missing extensions: <span class="code">' . implode(', ', $missingExtensions) . '</span>'
            ];
            $errors++;
        }

        // Check 3: Database Connection
        try {
            require_once __DIR__ . '/config/db.php';
            $testDsn = 'mysql:host=' . DB_HOST;
            $testPdo = new PDO($testDsn, DB_USER, DB_PASS);
            $checks[] = [
                'type' => 'success',
                'title' => 'Database Connection',
                'desc' => "Successfully connected to MySQL at <span class='code'>" . DB_HOST . "</span>. ✓"
            ];
            $success++;

            // Check if database exists
            $stmt = $testPdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
            if ($stmt->fetch()) {
                $checks[] = [
                    'type' => 'success',
                    'title' => 'Database Exists',
                    'desc' => "Database <span class='code'>" . DB_NAME . "</span> exists. ✓"
                ];
                $success++;

                // Check tables
                $testPdo->exec("USE `" . DB_NAME . "`");
                $stmt = $testPdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $requiredTables = ['users', 'student_profiles', 'company_profiles', 'tasks'];
                $missingTables = array_diff($requiredTables, $tables);

                if (empty($missingTables)) {
                    $checks[] = [
                        'type' => 'success',
                        'title' => 'Database Tables',
                        'desc' => 'All required tables exist (' . count($tables) . ' tables found). ✓'
                    ];
                    $success++;
                } else {
                    $checks[] = [
                        'type' => 'warning',
                        'title' => 'Database Tables',
                        'desc' => 'Missing tables: <span class="code">' . implode(', ', $missingTables) . '</span><br>Visit <a href="/">homepage</a> to auto-setup, or import database.sql manually.'
                    ];
                    $warnings++;
                }
            } else {
                $checks[] = [
                    'type' => 'warning',
                    'title' => 'Database Setup',
                    'desc' => "Database <span class='code'>" . DB_NAME . "</span> will be created automatically on first visit."
                ];
                $warnings++;
            }
        } catch (PDOException $e) {
            $checks[] = [
                'type' => 'error',
                'title' => 'Database Connection',
                'desc' => 'Failed to connect: <span class="code">' . htmlspecialchars($e->getMessage()) . '</span><br><br>
                         <strong>Solutions:</strong><br>
                         1. Make sure MySQL is running in XAMPP Control Panel<br>
                         2. Check credentials in <span class="code">config/db.php</span><br>
                         3. Default: host=localhost, user=root, password=(empty)'
            ];
            $errors++;
        } catch (Exception $e) {
            $checks[] = [
                'type' => 'error',
                'title' => 'Configuration',
                'desc' => 'Error loading config: <span class="code">' . htmlspecialchars($e->getMessage()) . '</span>'
            ];
            $errors++;
        }

        // Check 4: File Permissions
        $writablePaths = [
            'storage/sessions' => __DIR__ . '/storage/sessions',
            'assets/uploads/profiles' => __DIR__ . '/assets/uploads/profiles'
        ];
        $permissionIssues = [];
        foreach ($writablePaths as $label => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            if (!is_writable($path)) {
                $permissionIssues[] = $label;
            }
        }
        if (empty($permissionIssues)) {
            $checks[] = [
                'type' => 'success',
                'title' => 'File Permissions',
                'desc' => 'All required directories are writable. ✓'
            ];
            $success++;
        } else {
            $checks[] = [
                'type' => 'warning',
                'title' => 'File Permissions',
                'desc' => 'These directories need write permission: <span class="code">' . implode(', ', $permissionIssues) . '</span>'
            ];
            $warnings++;
        }

        // Check 5: .htaccess
        if (file_exists(__DIR__ . '/.htaccess')) {
            $checks[] = [
                'type' => 'success',
                'title' => '.htaccess File',
                'desc' => 'URL rewriting configuration exists. ✓'
            ];
            $success++;
        } else {
            $checks[] = [
                'type' => 'warning',
                'title' => '.htaccess File',
                'desc' => '.htaccess file is missing. Create it in the root directory for clean URLs.'
            ];
            $warnings++;
        }

        // Check 6: mod_rewrite (Apache)
        if (function_exists('apache_get_modules')) {
            if (in_array('mod_rewrite', apache_get_modules())) {
                $checks[] = [
                    'type' => 'success',
                    'title' => 'Apache mod_rewrite',
                    'desc' => 'URL rewriting module is enabled. ✓'
                ];
                $success++;
            } else {
                $checks[] = [
                    'type' => 'warning',
                    'title' => 'Apache mod_rewrite',
                    'desc' => 'mod_rewrite is not enabled. Enable it in httpd.conf and restart Apache.'
                ];
                $warnings++;
            }
        } else {
            $checks[] = [
                'type' => 'warning',
                'title' => 'Apache Detection',
                'desc' => 'Cannot detect Apache modules (might be running under different server).'
            ];
            $warnings++;
        }

        // Display checks
        foreach ($checks as $check) {
            $iconSymbol = $check['type'] === 'success' ? '✓' : ($check['type'] === 'error' ? '✗' : '⚠');
            echo '<div class="check-item ' . $check['type'] . '">';
            echo '<div class="check-title">';
            echo '<span class="icon ' . $check['type'] . '">' . $iconSymbol . '</span>';
            echo htmlspecialchars($check['title']);
            echo '</div>';
            echo '<div class="check-desc">' . $check['desc'] . '</div>';
            echo '</div>';
        }

        // Summary
        $total = $success + $warnings + $errors;
        $status = $errors === 0 ? 'Ready' : 'Issues Found';
        $statusEmoji = $errors === 0 ? '✅' : '⚠️';
        ?>

        <div class="summary">
            <h2><?= $statusEmoji ?> <?= $status ?></h2>
            <p>
                <strong><?= $success ?></strong> passed &bull;
                <strong><?= $warnings ?></strong> warnings &bull;
                <strong><?= $errors ?></strong> errors
            </p>
            <?php if ($errors === 0): ?>
                <p style="margin-top: 15px;">Your TalentProve installation is ready to use!</p>
            <?php else: ?>
                <p style="margin-top: 15px;">Please fix the errors above before using TalentProve.</p>
            <?php endif; ?>
        </div>

        <div class="actions">
            <?php if ($errors === 0): ?>
                <a href="/" class="btn btn-primary">Go to Homepage</a>
                <a href="/auth/register.php" class="btn btn-secondary">Create Account</a>
            <?php else: ?>
                <a href="/check.php" class="btn btn-primary">Refresh Check</a>
                <a href="/SETUP_INSTRUCTIONS.md" class="btn btn-secondary">Read Setup Guide</a>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f7fafc; border-radius: 8px; border: 2px dashed #cbd5e0;">
            <strong style="display: block; margin-bottom: 10px;">📋 System Information:</strong>
            <pre><?php
echo "PHP Version:     " . PHP_VERSION . "\n";
echo "Server:          " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root:   " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current Script:  " . __FILE__ . "\n";
echo "Server Name:     " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "Server Port:     " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n";
echo "Request URI:     " . ($_SERVER['REQUEST_URI'] ?? '/') . "\n";
            ?></pre>
        </div>
    </div>
</body>
</html>