<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$role = $_POST['role'] ?? '';
if (!in_array($role, ['student', 'company'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']);
    exit;
}

$pdo = getDB();

function normalizeEmail($email) {
    return strtolower(trim((string)$email));
}

function isGmailAddress($email) {
    return (bool)preg_match('/^[a-z0-9._%+\-]+@gmail\.com$/', $email);
}

function normalizePlainName($name) {
    return preg_replace('/\s+/', ' ', trim((string)$name));
}

function isPlainName($name) {
    return (bool)preg_match('/^[A-Za-z][A-Za-z0-9]*(?: [A-Za-z0-9]+)*$/', $name);
}

if ($role === 'student') {
    $name     = normalizePlainName($_POST['name'] ?? '');
    $email    = normalizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $skills   = trim($_POST['skills'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $portfolio = trim($_POST['portfolio_link'] ?? '');

    if (!$name || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Name, email, and password are required.']);
        exit;
    }
    if (!isPlainName($name)) {
        echo json_encode(['success' => false, 'message' => 'Name must start with a letter and can use letters, numbers, and spaces only.']);
        exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        exit;
    }
    if (!isGmailAddress($email)) {
        echo json_encode(['success' => false, 'message' => 'Please use a valid @gmail.com address.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
        $stmt->execute([$name, $email, $hash]);
        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO student_profiles (user_id, skills, bio, portfolio_link) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $skills, $bio, $portfolio]);

        $pdo->commit();

        $_SESSION['user_id'] = $userId;
        $_SESSION['name']    = $name;
        $_SESSION['role']    = 'student';

        echo json_encode(['success' => true, 'message' => 'Account created!', 'redirect' => '/Dashboard/student.php']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
    }

} else {
    $companyName = normalizePlainName($_POST['company_name'] ?? '');
    $email       = normalizeEmail($_POST['company_email'] ?? '');
    $password    = $_POST['company_password'] ?? '';
    $industry    = trim($_POST['industry'] ?? '');
    $website     = trim($_POST['website'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$companyName || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Company name, email, and password are required.']);
        exit;
    }
    if (!isPlainName($companyName)) {
        echo json_encode(['success' => false, 'message' => 'Company name must start with a letter and can use letters, numbers, and spaces only.']);
        exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        exit;
    }
    if (!isGmailAddress($email)) {
        echo json_encode(['success' => false, 'message' => 'Please use a valid @gmail.com address.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'company', 'active')");
        $stmt->execute([$companyName, $email, $hash]);
        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO company_profiles (user_id, company_name, industry, website, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $companyName, $industry, $website, $description]);

        $pdo->commit();

        $_SESSION['user_id'] = $userId;
        $_SESSION['name']    = $companyName;
        $_SESSION['role']    = 'company';

        echo json_encode(['success' => true, 'message' => 'Company account created!', 'redirect' => '/Dashboard/company.php']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
    }
}
