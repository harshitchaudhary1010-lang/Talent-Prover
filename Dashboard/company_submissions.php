<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('company');

$pdo = getDB();
$status = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'pending', 'reviewed', 'shortlisted', 'rejected'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

$sql = "SELECT s.*, t.title, u.name AS student_name, u.email AS student_email, sp.skills, sp.portfolio_link
    FROM submissions s
    JOIN tasks t ON t.id = s.task_id
    JOIN users u ON u.id = s.student_id
    LEFT JOIN student_profiles sp ON sp.user_id = u.id
    WHERE t.company_id = ?";
$params = [$_SESSION['user_id']];
if ($status !== 'all') {
    $sql .= " AND s.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY s.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Submissions - TalentProve</title>
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
        <a class="sidebar-link" href="/dashboard/company_tasks.php"><i class="fa-solid fa-list-check"></i> Tasks</a>
        <a class="sidebar-link active" href="/dashboard/company_submissions.php"><i class="fa-solid fa-user-check"></i> Submissions</a>
        <a class="sidebar-link" href="/dashboard/company_messages.php"><i class="fa-solid fa-comment-dots"></i> Messages</a>
        <a class="sidebar-link" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<main class="dashboard-main company-main fade-in">
    <header class="company-hero">
        <div>
            <p>Candidate proof</p>
            <h1><?= $status === 'shortlisted' ? 'Shortlisted work' : 'Company submissions' ?></h1>
            <span>Review candidate submissions and open their proof links.</span>
        </div>
        <button class="btn-soft lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    </header>

    <section class="company-page-section">
        <div class="company-section-head">
            <div>
                <span>Review queue</span>
                <h2><?= ucfirst($status) ?> submissions</h2>
            </div>
            <div class="company-filter-tabs">
                <?php foreach (['all', 'pending', 'reviewed', 'shortlisted', 'rejected'] as $tab): ?>
                    <a class="<?= $status === $tab ? 'active' : '' ?>" href="/dashboard/company_submissions.php<?= $tab === 'all' ? '' : '?status=' . $tab ?>"><?= ucfirst($tab) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="company-submission-list">
            <?php if (!$submissions): ?>
                <div class="company-empty"><i class="fa-solid fa-inbox"></i><p>No submissions found.</p></div>
            <?php endif; ?>
            <?php foreach ($submissions as $item): ?>
                <article class="company-submission-card">
                    <div class="company-submission-main">
                        <div class="company-candidate-avatar"><?= htmlspecialchars(strtoupper(substr($item['student_name'] ?: 'S', 0, 1))) ?></div>
                        <div>
                            <h3><?= htmlspecialchars($item['student_name']) ?><span><?= htmlspecialchars($item['title']) ?></span></h3>
                            <p><?= htmlspecialchars($item['student_email']) ?> - <?= htmlspecialchars($item['skills'] ?: 'No skills listed') ?></p>
                            <div class="company-submission-links">
                                <a href="<?= htmlspecialchars($item['submission_link']) ?>" target="_blank" rel="noopener">Open work <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                <a href="/dashboard/company_messages.php?student_id=<?= (int)$item['student_id'] ?>&student_name=<?= urlencode($item['student_name']) ?>">Message <i class="fa-solid fa-comment-dots"></i></a>
                                <?php if (!empty($item['portfolio_link'])): ?>
                                    <a href="<?= htmlspecialchars($item['portfolio_link']) ?>" target="_blank" rel="noopener">Portfolio <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="company-review-actions">
                        <span class="badge badge-<?= htmlspecialchars($item['status']) ?>" id="badge-<?= $item['id'] ?>"><?= htmlspecialchars($item['status']) ?></span>
                        <div class="company-status-buttons">
                            <?php foreach (['reviewed','shortlisted','rejected'] as $s): ?>
                                <button class="btn-soft min-h-0 px-3 py-2 text-sm" onclick="setStatus(<?= $item['id'] ?>, '<?= $s ?>', this)"><?= $s ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script src="/assets/js/main.js"></script>
<script>
async function setStatus(id, status, btn) {
    const fd = new FormData();
    fd.append('action', 'status');
    fd.append('id', id);
    fd.append('status', status);
    const data = await postForm('/api/submissions.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        const badge = document.getElementById('badge-' + id);
        if (badge) {
            badge.className = 'badge badge-' + status;
            badge.textContent = status;
        }
    }
}
</script>
</body>
</html>
