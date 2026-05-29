<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('company');

$pdo = getDB();

function getCompanyProfile(PDO $pdo) {
    $stmt = $pdo->prepare("SELECT u.*, cp.company_name, cp.industry, cp.website, cp.description, cp.logo
        FROM users u
        LEFT JOIN company_profiles cp ON cp.user_id = u.id
        WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

$company = getCompanyProfile($pdo);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $password = $_POST['password'] ?? '';
    $logo = $company['logo'] ?? null;

    try {
        if ($name === '' || $email === '' || $companyName === '') {
            throw new RuntimeException('Name, email, and company name are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid email address.');
        }
        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Enter a valid website URL.');
        }
        if ($password !== '' && strlen($password) < 8) {
            throw new RuntimeException('New password must be at least 8 characters.');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            throw new RuntimeException('That email is already used by another account.');
        }

        if (!empty($_FILES['logo']['name'])) {
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Logo upload failed.');
            }
            if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
                throw new RuntimeException('Logo must be 2MB or smaller.');
            }
            $info = getimagesize($_FILES['logo']['tmp_name']);
            if (!$info) {
                throw new RuntimeException('Upload a valid image file.');
            }
            $extensions = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
            ];
            if (!isset($extensions[$info[2]])) {
                throw new RuntimeException('Use a JPG, PNG, or WebP logo.');
            }
            $uploadDir = dirname(__DIR__) . '/assets/uploads/profiles';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                throw new RuntimeException('Could not create profile upload folder.');
            }
            $filename = 'company-' . (int)$_SESSION['user_id'] . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$info[2]];
            $target = $uploadDir . '/' . $filename;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                throw new RuntimeException('Could not save logo.');
            }
            $logo = '/assets/uploads/profiles/' . $filename;
        }

        $pdo->beginTransaction();
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $_SESSION['user_id']]);
        }

        $stmt = $pdo->prepare("INSERT INTO company_profiles (user_id, company_name, industry, website, description, logo)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE company_name = VALUES(company_name), industry = VALUES(industry), website = VALUES(website), description = VALUES(description), logo = VALUES(logo)");
        $stmt->execute([$_SESSION['user_id'], $companyName, $industry, $website, $description, $logo]);
        $pdo->commit();

        $_SESSION['name'] = $name;
        $company = getCompanyProfile($pdo);
        $messageType = 'success';
        $message = 'Company profile updated successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $messageType = 'error';
        $message = $e->getMessage();
    }
}

$logoPreview = !empty($company['logo'])
    ? '<img src="' . htmlspecialchars($company['logo']) . '" alt="Company logo">'
    : htmlspecialchars(strtoupper(substr($company['company_name'] ?: $company['name'], 0, 1)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Profile - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="dashboard-shell company-dashboard">
<aside id="sidebar" class="sidebar">
    <a class="brand-mark mb-8" href="/">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
    </a>
    <nav class="grid gap-2">
        <a class="sidebar-link" href="/dashboard/company.php"><i class="fa-solid fa-chart-simple"></i> Overview</a>
        <a class="sidebar-link active" href="/dashboard/company_profile.php"><i class="fa-solid fa-building-user"></i> Profile</a>
        <a class="sidebar-link" href="/dashboard/company_tasks.php"><i class="fa-solid fa-list-check"></i> Tasks</a>
        <a class="sidebar-link" href="/dashboard/company_submissions.php"><i class="fa-solid fa-user-check"></i> Submissions</a>
        <a class="sidebar-link" href="/dashboard/company_messages.php"><i class="fa-solid fa-comment-dots"></i> Messages</a>
        <a class="sidebar-link" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<main class="dashboard-main company-main fade-in">
    <header class="company-hero">
        <div>
            <p>Account settings</p>
            <h1>Company profile</h1>
            <span>Update your company identity, website, description, and logo.</span>
        </div>
        <div class="company-hero-actions">
            <button class="btn-soft lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <a class="btn-primary" href="/dashboard/company.php"><i class="fa-solid fa-chart-simple"></i> Dashboard</a>
        </div>
    </header>

    <section class="student-profile-grid student-page-section">
        <article class="company-profile-card">
            <div class="profile-preview">
                <div class="profile-picture-preview"><?= $logoPreview ?></div>
                <div>
                    <h2><?= htmlspecialchars($company['company_name'] ?: $company['name']) ?></h2>
                    <p><?= htmlspecialchars($company['email']) ?></p>
                    <p><?= htmlspecialchars($company['industry'] ?: 'Industry not set') ?></p>
                    <?php if (!empty($company['website'])): ?>
                        <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" rel="noopener">Website <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <article class="company-profile-card">
            <div class="company-panel-head">
                <div>
                    <span>Edit profile</span>
                    <h2>Company details</h2>
                </div>
            </div>
            <?php if ($message): ?>
                <div class="<?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?> mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="profile-form-grid">
                <div class="form-group">
                    <label class="form-label">Account name</label>
                    <input class="form-input" name="name" value="<?= htmlspecialchars($company['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($company['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company name</label>
                    <input class="form-input" name="company_name" value="<?= htmlspecialchars($company['company_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Industry</label>
                    <input class="form-input" name="industry" value="<?= htmlspecialchars($company['industry'] ?? '') ?>" placeholder="SaaS, Fintech, Education">
                </div>
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input class="form-input" type="url" name="website" value="<?= htmlspecialchars($company['website'] ?? '') ?>" placeholder="https://company.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Company logo</label>
                    <input class="form-input" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="form-group profile-form-wide">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" name="description" placeholder="Tell candidates what your company builds and values."><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group profile-form-wide">
                    <label class="form-label">New password</label>
                    <input class="form-input" type="password" name="password" placeholder="Leave blank to keep current password">
                </div>
                <button class="student-submit-btn profile-form-wide" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save company profile</button>
            </form>
        </article>
    </section>
</main>
<script src="/assets/js/main.js"></script>
</body>
</html>
