<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'talentprover');
define('DB_USER', 'root');
define('DB_PASS', '');

function dbOptions() {
    return [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
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

function databaseNeedsSetup($pdo) {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    return $stmt->fetchColumn() === false;
}

function bootstrapDatabase() {
    $serverDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $pdo = new PDO($serverDsn, DB_USER, DB_PASS, dbOptions());
    $database = str_replace('`', '``', DB_NAME);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$database}`");

    if (!databaseNeedsSetup($pdo)) {
        return;
    }

    $schemaFile = dirname(__DIR__) . '/database.sql';
    if (!is_file($schemaFile)) {
        throw new RuntimeException('database.sql was not found.');
    }

    $schema = file_get_contents($schemaFile);
    $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?talentprove`?\s+CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\s*;?/i', '', $schema);
    $schema = preg_replace('/USE\s+`?talentprove`?\s*;?/i', '', $schema);

    foreach (splitSqlStatements($schema) as $statement) {
        $pdo->exec($statement);
    }
}

function failDatabaseConnection($message) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $isApi = strpos($requestUri, '/api/') !== false;

    // Always send JSON from API endpoints regardless of Accept header
    if ($isApi) {
        // Make sure nothing was sent yet
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        die(json_encode(['success' => false, 'message' => $message]));
    }

    http_response_code(500);
    die(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
}

function getDB() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        bootstrapDatabase();
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, dbOptions());
        return $pdo;
    } catch (PDOException $e) {
        failDatabaseConnection('Database connection failed. Please start MySQL/MariaDB and refresh the page.');
    } catch (Throwable $e) {
        failDatabaseConnection('Database setup failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}
