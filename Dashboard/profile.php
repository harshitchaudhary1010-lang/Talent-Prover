<?php
require_once '../config/db.php';
require_once '../config/session.php';
require_once 'student_layout.php';
requireExactRole('student');

$pdo = getDB();
$profile = getStudentProfile($pdo);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $portfolio = trim($_POST['portfolio_link'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $password = $_POST['password'] ?? '';
    $profileImage = $profile['profile_image'] ?? null;

    try {
        if ($name === '' || $email === '') {
            throw new RuntimeException('Name and email are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid email address.');
        }
        if ($portfolio !== '' && !filter_var($portfolio, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Enter a valid portfolio URL.');
        }
        if ($password !== '' && strlen($password) < 8) {
            throw new RuntimeException('New password must be at least 8 characters.');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            throw new RuntimeException('That email is already used by another account.');
        }

        if (!empty($_FILES['profile_image']['name'])) {
            if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Profile image upload failed.');
            }
            if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                throw new RuntimeException('Profile image must be 2MB or smaller.');
            }
            $info = getimagesize($_FILES['profile_image']['tmp_name']);
            if (!$info) {
                throw new RuntimeException('Upload a valid image file.');
            }
            $extensions = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
            ];
            if (!isset($extensions[$info[2]])) {
                throw new RuntimeException('Use a JPG, PNG, or WebP profile image.');
            }
            $uploadDir = dirname(__DIR__) . '/assets/uploads/profiles';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                throw new RuntimeException('Could not create profile upload folder.');
            }
            $filename = 'student-' . (int)$_SESSION['user_id'] . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$info[2]];
            $target = $uploadDir . '/' . $filename;
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                throw new RuntimeException('Could not save profile image.');
            }
            $profileImage = '/assets/uploads/profiles/' . $filename;
        }

        $pdo->beginTransaction();
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $_SESSION['user_id']]);
        }

        $stmt = $pdo->prepare("UPDATE student_profiles SET skills = ?, bio = ?, portfolio_link = ?, profile_image = ? WHERE user_id = ?");
        $stmt->execute([$skills, $bio, $portfolio, $profileImage, $_SESSION['user_id']]);
        $pdo->commit();

        $_SESSION['name'] = $name;
        $profile = getStudentProfile($pdo);
        $messageType = 'success';
        $message = 'Profile updated successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $messageType = 'error';
        $message = $e->getMessage();
    }
}

$avatar = !empty($profile['profile_image'])
    ? '<img src="' . htmlspecialchars($profile['profile_image']) . '" alt="Profile picture">'
    : htmlspecialchars(strtoupper(substr($profile['name'], 0, 1)));
?>
<?php studentPageHead('Profile'); ?>
<body class="student-portal">
<div class="student-layout">
    <?php renderStudentSidebar('profile'); ?>
    <main class="student-main">
        <header class="student-topbar">
            <button class="student-menu-btn lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="student-topbar-profile">
                <div class="student-topbar-avatar"><?= $avatar ?></div>
                <div>
                    <span class="student-eyebrow">Account settings</span>
                    <h1>Profile</h1>
                    <p>Update your details, portfolio, skills, and profile picture.</p>
                </div>
            </div>
            <a class="student-action d-none d-md-inline-flex" href="/dashboard/tasks.php"><i class="fa-solid fa-briefcase"></i> Browse tasks</a>
        </header>

        <section class="student-profile-grid student-page-section">
            <article class="student-panel">
                <div class="profile-preview">
                    <div class="profile-picture-preview"><?= $avatar ?></div>
                    <div>
                        <h2><?= htmlspecialchars($profile['name']) ?></h2>
                        <p><?= htmlspecialchars($profile['email']) ?></p>
                        <?php if (!empty($profile['portfolio_link'])): ?>
                            <a href="<?= htmlspecialchars($profile['portfolio_link']) ?>" target="_blank" rel="noopener">Portfolio <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <article class="student-panel">
                <div class="student-panel-head">
                    <div>
                        <span>Edit profile</span>
                        <h2>Your details</h2>
                    </div>
                </div>
                <?php if ($message): ?>
                    <div class="<?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?> mb-4"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="profile-form-grid">
                    <div class="form-group">
                        <label class="form-label">Full name</label>
                        <input class="form-input" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Skills</label>
                        <input class="form-input" name="skills" value="<?= htmlspecialchars($profile['skills'] ?? '') ?>" placeholder="HTML, CSS, PHP">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Portfolio link</label>
                        <input class="form-input" type="url" name="portfolio_link" value="<?= htmlspecialchars($profile['portfolio_link'] ?? '') ?>" placeholder="https://yourportfolio.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profile picture</label>
                        <input class="form-input" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New password</label>
                        <input class="form-input" type="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group profile-form-wide">
                        <label class="form-label">Bio</label>
                        <textarea class="form-textarea" name="bio" placeholder="Tell companies about yourself."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                    </div>
                    <button class="student-submit-btn profile-form-wide" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save profile</button>
                </form>
            </article>
        </section>
    </main>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
