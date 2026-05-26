<?php
function getStudentProfile(PDO $pdo) {
    $stmt = $pdo->prepare("SELECT u.*, sp.skills, sp.bio, sp.portfolio_link, sp.profile_image
        FROM users u
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function renderStudentSidebar($active = 'overview') {
    $items = [
        ['overview', '/dashboard/student.php', 'fa-border-all', 'Overview'],
        ['tasks', '/dashboard/tasks.php', 'fa-briefcase', 'Available Tasks'],
        ['submissions', '/dashboard/submissions.php', 'fa-paper-plane', 'My Submissions'],
        ['messages', '/dashboard/messages.php', 'fa-comment-dots', 'Messages'],
        ['profile', '/dashboard/profile.php', 'fa-user', 'Profile'],
        ['logout', '/dashboard/logout.php', 'fa-right-from-bracket', 'Logout'],
    ];
    ?>
    <aside id="sidebar" class="student-sidebar">
        <a class="student-logo" href="/">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo">
        </a>
        <nav class="student-menu">
            <?php foreach ($items as $item): ?>
                <a class="<?= $active === $item[0] ? 'active' : '' ?>" href="<?= htmlspecialchars($item[1]) ?>">
                    <i class="fa-solid <?= htmlspecialchars($item[2]) ?>"></i><span><?= htmlspecialchars($item[3]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <?php
}

function studentPageHead($title) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - TalentProve</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <?php
}
