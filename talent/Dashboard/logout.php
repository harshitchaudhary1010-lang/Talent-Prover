<?php
require_once '../config/session.php';
requireExactRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logout - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="student-portal">
<main class="logout-confirm-page">
    <section class="logout-confirm-card">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo">
        <i class="fa-solid fa-right-from-bracket"></i>
        <h1>Log out?</h1>
        <p>You can sign in again anytime to continue tracking tasks and submissions.</p>
        <form method="POST" class="logout-actions">
            <a class="student-detail-btn" href="/dashboard/student.php">Stay logged in</a>
            <button class="student-submit-btn" type="submit">Logout</button>
        </form>
    </section>
</main>
<script src="/assets/js/main.js"></script>
</body>
</html>
