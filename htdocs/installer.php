<?php
$rootDir = __DIR__;
$configFile = $rootDir . '/config/db.php';
$schemaFile = $rootDir . '/database.sql';
$storageDir = $rootDir . '/storage';
$lockFile = $storageDir . '/installed.lock';

$isInstalled = is_file($lockFile);
$message = '';
$messageType = 'info';
$defaults = [
    'host' => 'localhost',
    'database' => 'talentprove',
    'username' => 'root',
    'password' => '',
];

if (is_file($configFile)) {
    $configText = file_get_contents($configFile);
    foreach (['DB_HOST' => 'host', 'DB_NAME' => 'database', 'DB_USER' => 'username', 'DB_PASS' => 'password'] as $constant => $key) {
        if (preg_match("/define\\(\\s*['\"]" . preg_quote($constant, '/') . "['\"]\\s*,\\s*['\"](.*?)['\"]\\s*\\)/", $configText, $matches)) {
            $defaults[$key] = stripcslashes($matches[1]);
        }
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sqlQuote($value) {
    return "'" . str_replace("'", "\\'", $value) . "'";
}
function splitSqlStatements($sql) {
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $inString = false;
    $stringChar = '';
    $lineComment = false;
    $blockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if ($lineComment) {
            if ($char === "\n") {
                $lineComment = false;
                $buffer .= $char;
            }
            continue;
        }

        if ($blockComment) {
            if ($char === '*' && $next === '/') {
                $blockComment = false;
                $i++;
            }
            continue;
        }

        if (!$inString && $char === '-' && $next === '-') {
            $lineComment = true;
            $i++;
            continue;
        }

        if (!$inString && $char === '/' && $next === '*') {
            $blockComment = true;
            $i++;
            continue;
        }

        if (($char === "'" || $char === '"') && (!$inString || $stringChar === $char)) {
            $escaped = $i > 0 && $sql[$i - 1] === '\\';
            if (!$escaped) {
                $inString = !$inString;
                $stringChar = $inString ? $char : '';
            }
        }

        if (!$inString && $char === ';') {
            $statement = trim($buffer);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $statement = trim($buffer);
    if ($statement !== '') {
        $statements[] = $statement;
    }

    return $statements;
}

function writeDatabaseConfig($path, $host, $database, $username, $password) {
    $contents = "<?php\n"
        . "define('DB_HOST', " . var_export($host, true) . ");\n"
        . "define('DB_NAME', " . var_export($database, true) . ");\n"
        . "define('DB_USER', " . var_export($username, true) . ");\n"
        . "define('DB_PASS', " . var_export($password, true) . ");\n\n"
        . "function getDB() {\n"
        . "    static \$pdo = null;\n"
        . "    if (\$pdo === null) {\n"
        . "        try {\n"
        . "            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";\n"
        . "            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [\n"
        . "                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
        . "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
        . "                PDO::ATTR_EMULATE_PREPARES   => false,\n"
        . "            ]);\n"
        . "        } catch (PDOException \$e) {\n"
        . "            http_response_code(500);\n"
        . "            \$accept = \$_SERVER['HTTP_ACCEPT'] ?? '';\n"
        . "            \$requestUri = \$_SERVER['REQUEST_URI'] ?? '';\n"
        . "            if (stripos(\$accept, 'application/json') !== false || strpos(\$requestUri, '/api/') === 0) {\n"
        . "                header('Content-Type: application/json');\n"
        . "                die(json_encode(['success' => false, 'message' => 'Database connection failed. Run installer.php and check config/db.php.']));\n"
        . "            }\n"
        . "            die('Database connection failed. Run <a href=\"/installer.php\">installer.php</a> and check config/db.php.');\n"
        . "        }\n"
        . "    }\n"
        . "    return \$pdo;\n"
        . "}\n";

    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Could not write config/db.php. Check folder permissions.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isInstalled) {
    $host = trim($_POST['host'] ?? $defaults['host']);
    $database = trim($_POST['database'] ?? $defaults['database']);
    $username = trim($_POST['username'] ?? $defaults['username']);
    $password = (string)($_POST['password'] ?? '');
    $defaults = compact('host', 'database', 'username', 'password');

    try {
        if ($host === '' || $database === '' || $username === '') {
            throw new RuntimeException('Host, database name, and username are required.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new RuntimeException('Database name can contain only letters, numbers, and underscores.');
        }
        if (!is_file($schemaFile)) {
            throw new RuntimeException('database.sql was not found.');
        }
        if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true)) {
            throw new RuntimeException('Could not create the storage folder.');
        }

        $serverDsn = 'mysql:host=' . $host . ';charset=utf8mb4';
        $pdo = new PDO($serverDsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $database) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . str_replace('`', '``', $database) . '`');

        $schema = file_get_contents($schemaFile);
        $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?talentprove`?\s+CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\s*;?/i', '', $schema);
        $schema = preg_replace('/USE\s+`?talentprove`?\s*;?/i', '', $schema);
        foreach (splitSqlStatements($schema) as $statement) {
            $pdo->exec($statement);
        }

        writeDatabaseConfig($configFile, $host, $database, $username, $password);
        file_put_contents($lockFile, 'Installed on ' . date('c') . PHP_EOL, LOCK_EX);

        $isInstalled = true;
        $messageType = 'success';
        $message = 'Installation complete. Demo login password is password.';
    } catch (Throwable $e) {
        $messageType = 'error';
        $message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TalentProve Installer</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<main class="mx-auto flex min-h-screen max-w-5xl items-center px-5 py-10">
    <section class="grid w-full gap-6 rounded-[28px] border border-white/10 bg-white/5 p-6 shadow-2xl md:grid-cols-[.9fr_1.1fr] md:p-8">
        <div class="flex flex-col justify-between gap-8 rounded-2xl bg-emerald-500 p-6 text-slate-950">
            <div>
                <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="mb-6 h-14 w-14 rounded-xl object-cover">
                <p class="text-sm font-black uppercase tracking-wide text-emerald-950/70">One-time setup</p>
                <h1 class="mt-3 text-4xl font-black">TalentProve database installer</h1>
                <p class="mt-4 leading-7 text-emerald-950/80">This creates the MySQL database, imports demo data, writes the database config, and then locks itself.</p>
            </div>
            <div class="grid gap-3 text-sm font-bold">
                <span><i class="fa-solid fa-database mr-2"></i> Creates database and tables</span>
                <span><i class="fa-solid fa-user-shield mr-2"></i> Adds admin, student, and company demos</span>
                <span><i class="fa-solid fa-lock mr-2"></i> Writes storage/installed.lock</span>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 text-slate-950">
            <?php if ($message): ?>
                <div class="mb-5 rounded-xl px-4 py-3 text-sm font-bold <?= $messageType === 'success' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?>">
                    <?= h($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($isInstalled): ?>
                <div class="grid gap-4">
                    <h2 class="text-2xl font-black">System is installed</h2>
                    <p class="leading-7 text-slate-600">For security, this installer is locked. To reinstall intentionally, delete <strong>storage/installed.lock</strong> first.</p>
                    <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        <p class="font-black text-slate-950">Demo accounts</p>
                        <p>admin@talentprove.com / password</p>
                        <p>student@demo.com / password</p>
                        <p>company@demo.com / password</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a class="btn-primary" href="/auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                        <a class="btn-secondary" href="/"><i class="fa-solid fa-house"></i> Home</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="grid gap-4">
                    <div>
                        <h2 class="text-2xl font-black">Database settings</h2>
                        <p class="mt-1 text-sm text-slate-500">Use your local XAMPP/MySQL credentials.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold">
                            Host
                            <input class="form-input" name="host" value="<?= h($defaults['host']) ?>" required>
                        </label>
                        <label class="grid gap-2 text-sm font-bold">
                            Database
                            <input class="form-input" name="database" value="<?= h($defaults['database']) ?>" required>
                        </label>
                        <label class="grid gap-2 text-sm font-bold">
                            Username
                            <input class="form-input" name="username" value="<?= h($defaults['username']) ?>" required>
                        </label>
                        <label class="grid gap-2 text-sm font-bold">
                            Password
                            <input class="form-input" name="password" type="password" value="<?= h($defaults['password']) ?>">
                        </label>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4 text-sm font-bold text-amber-900">
                        This imports database.sql. Existing TalentProve tables in this database will be reset.
                    </div>
                    <button class="btn-primary" type="submit"><i class="fa-solid fa-wand-magic-sparkles"></i> Install now</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
