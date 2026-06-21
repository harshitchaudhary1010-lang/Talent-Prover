<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$pdo = getDB();
$query = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT t.*, cp.company_name, cp.industry
    FROM tasks t
    JOIN company_profiles cp ON cp.user_id = t.company_id
    WHERE t.status = 'active'
    AND (t.deadline IS NULL OR t.deadline >= CURDATE())";

if ($query !== '') {
    $sql .= " AND (t.title LIKE ? OR t.description LIKE ? OR t.required_skills LIKE ? OR cp.company_name LIKE ? OR cp.industry LIKE ?)";
    $needle = '%' . $query . '%';
    $params = [$needle, $needle, $needle, $needle, $needle];
}

$sql .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$allTasksStmt = $pdo->query("SELECT t.required_skills, t.deadline, cp.company_name
    FROM tasks t
    JOIN company_profiles cp ON cp.user_id = t.company_id
    WHERE t.status = 'active'
    AND (t.deadline IS NULL OR t.deadline >= CURDATE())");
$allTasks = $allTasksStmt->fetchAll();
$companyCount = count(array_unique(array_filter(array_column($allTasks, 'company_name'))));
$openDeadlineCount = count(array_filter($allTasks, fn($task) => empty($task['deadline'])));
$skillCounts = [];
foreach ($allTasks as $task) {
    foreach (array_filter(array_map('trim', explode(',', $task['required_skills'] ?: ''))) as $skill) {
        $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
    }
}
arsort($skillCounts);
$popularSkills = array_slice(array_keys($skillCounts), 0, 6);

function taskDeadlineLabel($deadline) {
    if (!$deadline) {
        return 'Open deadline';
    }
    $date = new DateTime($deadline);
    $today = new DateTime('today');
    $diff = (int)$today->diff($date)->format('%r%a');
    $label = $date->format('M j, Y');
    if ($diff < 0) {
        return $label . ' - expired';
    }
    if ($diff === 0) {
        return $label . ' - due today';
    }
    if ($diff === 1) {
        return $label . ' - 1 day left';
    }
    return $label . ' - ' . $diff . ' days left';
}

function taskPostedLabel($createdAt) {
    if (!$createdAt) {
        return 'Recently posted';
    }
    $date = new DateTime($createdAt);
    return 'Posted ' . $date->format('M j');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tasks - TalentProve</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="page-bg public-tasks-page">
<div class="pattern-layer" aria-hidden="true"></div>

<header class="site-header sticky top-0 z-50 mx-auto flex max-w-7xl items-center justify-between px-5 py-2">
    <a href="/" class="brand-mark">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
    </a>
    <nav class="site-nav hidden items-center gap-7 text-sm font-bold text-slate-300 md:flex">
        <a href="/#features">Features</a>
        <a href="/tasks.php">Tasks</a>
        <a href="/#how">How it works</a>
        <a href="/#standards">Standards</a>
        <a href="/#team">Team</a>
    </nav>
    <div class="hidden items-center gap-3 md:flex">
        <?php if (isLoggedIn()): ?>
            <a class="btn-secondary" href="/Dashboard/<?= htmlspecialchars($_SESSION['role']) ?>.php">Dashboard</a>
        <?php else: ?>
            <a class="nav-login" href="/auth/login.php">Login</a>
            <a class="btn-primary" href="/auth/register.php">Create account</a>
        <?php endif; ?>
    </div>
    <button class="nav-toggle md:hidden" type="button" onclick="toggleTopNav()" aria-label="Open navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<div id="mobileNav" class="mobile-nav mx-auto hidden max-w-7xl px-5 md:hidden">
    <a href="/#features">Features</a>
    <a href="/tasks.php">Tasks</a>
    <a href="/#how">How it works</a>
    <a href="/#standards">Standards</a>
    <?php if (isLoggedIn()): ?>
        <a href="/Dashboard/<?= htmlspecialchars($_SESSION['role']) ?>.php">Dashboard</a>
    <?php else: ?>
        <a href="/auth/login.php">Login</a>
        <a href="/auth/register.php">Create account</a>
    <?php endif; ?>
</div>

<main class="public-tasks-main">
    <section class="public-task-hero">
        <div>
            <span class="section-kicker">Proof-of-work tasks</span>
            <h1>Browse real assignments before you apply.</h1>
            <p>Explore active tasks from companies. Choose one that matches your skills, create an account, and submit your best proof of work.</p>
            <div class="public-task-stats">
                <div><strong><?= count($allTasks) ?></strong><span>Active tasks</span></div>
                <div><strong><?= $companyCount ?></strong><span>Companies</span></div>
                <div><strong><?= $openDeadlineCount ?></strong><span>Open deadlines</span></div>
            </div>
            <form class="public-task-search" method="GET">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search skill, company, or task">
                <button type="submit">Search</button>
            </form>
            <?php if ($popularSkills): ?>
                <div class="public-task-tags">
                    <span>Popular skills</span>
                    <?php foreach ($popularSkills as $skill): ?>
                        <a href="/tasks.php?q=<?= urlencode($skill) ?>"><?= htmlspecialchars($skill) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="public-task-visual">
            <img src="/assets/images/task%202.png" alt="Browse real assignments preview">
        </div>
    </section>

    <section class="public-task-section">
        <aside class="public-task-sidebar">
            <div class="public-task-guide">
                <span>How to apply</span>
                <h2>Submit proof, not just a resume.</h2>
                <div>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <p>Pick a task that matches your strongest skills.</p>
                </div>
                <div>
                    <i class="fa-solid fa-user-plus"></i>
                    <p>Create your student profile and portfolio.</p>
                </div>
                <div>
                    <i class="fa-solid fa-paper-plane"></i>
                    <p>Submit a GitHub, Drive, or live demo link.</p>
                </div>
                <a class="btn-primary" href="/auth/register.php">Create account</a>
            </div>
        </aside>

        <div>
            <div class="public-task-section-head">
                <div>
                    <span><?= count($tasks) ?> active task<?= count($tasks) === 1 ? '' : 's' ?></span>
                    <h2><?= $query !== '' ? 'Results for "' . htmlspecialchars($query) . '"' : 'Available tasks' ?></h2>
                </div>
                <?php if ($query !== ''): ?>
                    <a href="/tasks.php">Clear search</a>
                <?php endif; ?>
            </div>

            <div class="public-task-grid">
                <?php if (!$tasks): ?>
                    <div class="public-task-empty">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>No active tasks matched your search.</p>
                        <a href="/tasks.php">View all tasks</a>
                    </div>
                <?php endif; ?>

                <?php foreach ($tasks as $index => $task): ?>
                <?php
                $skills = array_filter(array_map('trim', explode(',', $task['required_skills'] ?: 'Open skill set')));
                $initial = strtoupper(substr($task['company_name'] ?: 'C', 0, 1));
                ?>
                <article class="public-task-card <?= $index === 0 && $query === '' ? 'featured' : '' ?>">
                    <div class="public-task-top">
                        <div class="public-company-mark"><?= htmlspecialchars($initial) ?></div>
                        <div>
                            <span><?= htmlspecialchars($task['company_name']) ?></span>
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                        </div>
                    </div>
                    <?php if ($index === 0 && $query === ''): ?>
                        <div class="public-featured-badge"><i class="fa-solid fa-star"></i> Featured task</div>
                    <?php endif; ?>
                    <p><?= htmlspecialchars($task['description']) ?></p>
                    <div class="public-task-skills">
                        <?php foreach (array_slice($skills, 0, 4) as $skill): ?>
                            <span><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="public-task-meta">
                        <span><i class="fa-solid fa-building"></i><?= htmlspecialchars($task['industry'] ?: 'Verified company') ?></span>
                        <span><i class="fa-solid fa-calendar-days"></i><?= htmlspecialchars(taskDeadlineLabel($task['deadline'])) ?></span>
                        <span><i class="fa-solid fa-clock"></i><?= htmlspecialchars(taskPostedLabel($task['created_at'] ?? null)) ?></span>
                    </div>
                    <div class="public-task-actions">
                        <a class="btn-secondary" href="/auth/register.php">Create account</a>
                        <a class="btn-primary" href="/auth/register.php"><i class="fa-solid fa-paper-plane"></i> Apply with proof</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer relative z-10 overflow-hidden px-5 py-14 text-sm text-slate-400">
    <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1.2fr_.8fr_.8fr]">
        <div>
            <a href="/" class="brand-mark">
                <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
            </a>
            <p class="mt-4 max-w-md leading-7">A practical task-based hiring platform for companies and students.</p>
        </div>
        <div>
            <h3>Platform</h3>
            <a href="/#features">Features</a>
            <a href="/tasks.php">Tasks</a>
            <a href="/#how">Workflow</a>
        </div>
        <div>
            <h3>Accounts</h3>
            <a href="/auth/register.php">Create account</a>
            <a href="/auth/login.php">Login</a>
        </div>
    </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>
