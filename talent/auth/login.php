<?php
require_once '../config/db.php';
require_once '../config/session.php';

if (isLoggedIn()) {
    header('Location: /dashboard/' . $_SESSION['role'] . '.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                $error = 'Your account has been blocked. Contact support.';
            } elseif ($user['status'] === 'pending') {
                $error = 'Your company account is pending approval.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];
                header('Location: /dashboard/' . $user['role'] . '.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-bg login-page min-h-screen">

<nav class="auth-nav">
    <a class="auth-back-btn" href="/"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <a href="/" class="auth-nav-logo">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo">
    </a>
    <a class="auth-nav-action" href="/auth/register.php">Create account <i class="fa-solid fa-arrow-right"></i></a>
</nav>

<main class="login-shell">
    <section class="login-brand-panel">
        <a href="/" class="brand-mark">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
        </a>
        <div>
            <p class="section-kicker">Welcome to TalentProve</p>
            <h1>Hire and get hired through real work.</h1>
            <p class="login-brand-copy">Companies post practical tasks. Students submit proof. Teams shortlist with confidence.</p>
        </div>
        <div class="login-proof-grid">
            <div><i class="fa-solid fa-shield-check"></i><span>Secure sessions</span></div>
            <div><i class="fa-solid fa-list-check"></i><span>Role dashboards</span></div>
            <div><i class="fa-solid fa-bell"></i><span>Review updates</span></div>
        </div>
    </section>

<section class="login-card fade-in">
    <div class="text-center mb-8">
        <a href="/" class="brand-mark justify-center mb-6 md:hidden">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
        </a>
        <h1 class="text-3xl font-black text-slate-950 mb-2">Welcome back</h1>
        <p class="text-slate-500 text-sm font-bold">Sign in to continue to your workspace</p>
    </div>

    <?php if ($error): ?>
    <div class="alert-error mb-4">
        <i class="fa-solid fa-circle-exclamation mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group mt-4">
            <label class="form-label">Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" name="password" id="passwordField" class="form-input" placeholder="Enter your password" required>
                <button type="button" class="eye-toggle" onclick="togglePassword('passwordField', this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full mt-6">
            <span>Sign In</span>
            <i class="fa-solid fa-arrow-right ml-2"></i>
        </button>
    </form>

    <p class="text-center text-slate-500 text-sm mt-6">
        Don't have an account?
        <a href="/auth/register.php" class="text-emerald-700 hover:text-emerald-800 font-black transition-colors">Create one</a>
    </p>

    <div class="divider my-4"><span>Demo Accounts</span></div>
    <div class="demo-accounts">
        <button onclick="fillDemo('admin@talentprove.com','password')" class="demo-btn">
            <i class="fa-solid fa-shield-halved"></i> Admin
        </button>
        <button onclick="fillDemo('student@demo.com','password')" class="demo-btn">
            <i class="fa-solid fa-graduation-cap"></i> Student
        </button>
        <button onclick="fillDemo('company@demo.com','password')" class="demo-btn">
            <i class="fa-solid fa-building"></i> Company
        </button>
    </div>
</section>
</main>

<script src="/assets/js/main.js"></script>
<script>
function fillDemo(email, pass) {
    document.querySelector('[name=email]').value = email;
    document.querySelector('[name=password]').value = pass;
}
</script>
</body>
</html>
