<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('company');

$pdo = getDB();
$status = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'active', 'closed', 'draft'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

$sql = "SELECT t.*, (SELECT COUNT(*) FROM submissions WHERE task_id = t.id) AS sub_count
    FROM tasks t
    WHERE t.company_id = ?";
$params = [$_SESSION['user_id']];
if ($status !== 'all') {
    $sql .= " AND t.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Tasks - TalentProve</title>
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
        <a class="sidebar-link" href="/dashboard/company_profile.php"><i class="fa-solid fa-building-user"></i> Profile</a>
        <a class="sidebar-link active" href="/dashboard/company_tasks.php"><i class="fa-solid fa-list-check"></i> Tasks</a>
        <a class="sidebar-link" href="/dashboard/company_submissions.php"><i class="fa-solid fa-user-check"></i> Submissions</a>
        <a class="sidebar-link" href="/dashboard/company_messages.php"><i class="fa-solid fa-comment-dots"></i> Messages</a>
        <a class="sidebar-link" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<main class="dashboard-main company-main fade-in">
    <header class="company-hero">
        <div>
            <p>Published work</p>
            <h1><?= $status === 'active' ? 'Active tasks' : 'Company tasks' ?></h1>
            <span>Review every proof-of-work task your company has posted.</span>
        </div>
        <div class="company-hero-actions">
            <button class="btn-soft lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <a class="btn-primary" href="/dashboard/company.php"><i class="fa-solid fa-plus"></i> Post task</a>
        </div>
    </header>

    <section class="company-page-section">
        <div class="company-section-head">
            <div>
                <span>Task list</span>
                <h2><?= ucfirst($status) ?> tasks</h2>
            </div>
            <div class="company-filter-tabs">
                <?php foreach (['all', 'active', 'closed', 'draft'] as $tab): ?>
                    <a class="<?= $status === $tab ? 'active' : '' ?>" href="/dashboard/company_tasks.php<?= $tab === 'all' ? '' : '?status=' . $tab ?>"><?= ucfirst($tab) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="company-task-grid">
            <?php if (!$tasks): ?>
                <div class="company-empty"><i class="fa-solid fa-folder-open"></i><p>No tasks found.</p></div>
            <?php endif; ?>
            <?php foreach ($tasks as $task): ?>
                <article class="company-task-card">
                    <div class="company-task-top">
                        <div>
                            <span><?= htmlspecialchars($task['sub_count']) ?> submission<?= (int)$task['sub_count'] === 1 ? '' : 's' ?></span>
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                        </div>
                        <span class="badge badge-<?= htmlspecialchars($task['status']) ?>"><?= htmlspecialchars($task['status']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($task['description']) ?></p>
                    <div class="company-chip-list">
                        <?php foreach (array_filter(array_map('trim', explode(',', $task['required_skills'] ?: 'Open skill set'))) as $skill): ?>
                            <span><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="company-task-meta">
                        <span><i class="fa-solid fa-calendar-days"></i><?= htmlspecialchars($task['deadline'] ?: 'Open deadline') ?></span>
                        <span><i class="fa-solid fa-shield-check"></i>Proof-of-work brief</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script src="/assets/js/main.js"></script>
</body>
</html>
