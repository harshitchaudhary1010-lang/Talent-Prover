<?php
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }
    session_save_path($sessionPath);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        header('Location: /auth/login.php');
        exit;
    }
}

function requireAnyRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        header('Location: /auth/login.php');
        exit;
    }
}

function requireExactRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: /auth/login.php');
        exit;
    }
}

function currentUser() {
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['name'] ?? '',
        'role' => $_SESSION['role'] ?? '',
    ];
}

function redirectDashboard() {
    if (isLoggedIn()) {
        header('Location: /Dashboard/' . $_SESSION['role'] . '.php');
        exit;
    }
}
