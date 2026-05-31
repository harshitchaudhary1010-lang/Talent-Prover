<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('company');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT u.*, cp.company_name, cp.industry, cp.website, cp.description FROM users u LEFT JOIN company_profiles cp ON cp.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

$stats = [
    'tasks' => 0,
    'submissions' => 0,
    'shortlisted' => 0,
    'active' => 0,
];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE company_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['tasks'] = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE company_id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$stats['active'] = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions s JOIN tasks t ON t.id = s.task_id WHERE t.company_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['submissions'] = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions s JOIN tasks t ON t.id = s.task_id WHERE t.company_id = ? AND s.status = 'shortlisted'");
$stmt->execute([$_SESSION['user_id']]);
$stats['shortlisted'] = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Dashboard - TalentProve</title>
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
        <a class="sidebar-link active" href="#overview"><i class="fa-solid fa-chart-simple"></i> Overview</a>
        <a class="sidebar-link" href="/dashboard/company_profile.php"><i class="fa-solid fa-building-user"></i> Profile</a>
        <a class="sidebar-link" href="/dashboard/company_tasks.php"><i class="fa-solid fa-list-check"></i> Tasks</a>
        <a class="sidebar-link" href="/dashboard/company_submissions.php"><i class="fa-solid fa-user-check"></i> Submissions</a>
        <a class="sidebar-link" href="/dashboard/company_messages.php"><i class="fa-solid fa-comment-dots"></i> Messages</a>
        <a class="sidebar-link" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<main class="dashboard-main company-main fade-in">
    <header class="company-hero">
        <div>
            <p>Company dashboard</p>
            <h1><?= htmlspecialchars($company['company_name'] ?: $company['name']) ?></h1>
            <span><?= htmlspecialchars($company['industry'] ?: 'Industry not set') ?></span>
        </div>
        <div class="company-hero-actions">
            <button class="btn-soft lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <button class="btn-primary" onclick="openModal('taskModal')"><i class="fa-solid fa-plus"></i> Post task</button>
        </div>
    </header>

    <section id="overview" class="company-stats-grid">
        <?php foreach ([['Total tasks', $stats['tasks'], 'fa-briefcase', '/dashboard/company_tasks.php'], ['Total submissions', $stats['submissions'], 'fa-paper-plane', '/dashboard/company_submissions.php'], ['Shortlisted', $stats['shortlisted'], 'fa-star', '/dashboard/company_submissions.php?status=shortlisted'], ['Active tasks', $stats['active'], 'fa-bolt', '/dashboard/company_tasks.php?status=active']] as $stat): ?>
            <a class="company-stat-card" href="<?= htmlspecialchars($stat[3]) ?>">
                <i class="fa-solid <?= $stat[2] ?>"></i>
                <strong><?= $stat[1] ?></strong>
                <span><?= $stat[0] ?></span>
            </a>
        <?php endforeach; ?>
    </section>

    <section class="company-overview-grid company-notification-only">
        <article class="company-notification-card">
            <div class="company-panel-head">
                <div>
                    <span>Updates</span>
                    <h2>Notifications</h2>
                </div>
                <button onclick="markNotificationsRead()">Mark read</button>
            </div>
            <div id="notificationsList" class="company-notification-list"></div>
        </article>
    </section>

    <section id="tasks" class="company-page-section">
        <div class="company-section-head">
            <div>
                <span>Published work</span>
                <h2>Your tasks</h2>
            </div>
            <button class="btn-primary" onclick="openModal('taskModal')"><i class="fa-solid fa-plus"></i> New task</button>
        </div>
        <div id="tasksList" class="company-task-grid"></div>
    </section>

    <section id="submissions" class="company-page-section">
        <div class="company-section-head">
            <div>
                <span>Candidate proof</span>
                <h2>Submitted work</h2>
            </div>
        </div>
        <div id="submissionsList" class="company-submission-list"></div>
    </section>
</main>

<div id="taskModal" class="modal-backdrop" data-modal-backdrop>
    <div class="modal-panel p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-black">Post a new proof-of-work task</h2>
            <button class="btn-soft min-h-0 px-3 py-2" onclick="closeModal('taskModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="taskForm" class="grid gap-4">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label class="form-label">Task title</label>
                <input class="form-input" name="title" required placeholder="Build a responsive analytics widget">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-textarea" name="description" required placeholder="Explain the challenge, deliverables, and review criteria."></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Required skills</label>
                    <input class="form-input" name="required_skills" placeholder="HTML, CSS, JavaScript">
                </div>
                <div class="form-group">
                    <label class="form-label">Deadline</label>
                    <input class="form-input" name="deadline" type="date">
                </div>
            </div>
            <button class="btn-primary" type="submit"><i class="fa-solid fa-plus"></i> Publish task</button>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function formatCompanyDate(value) {
    if (!value) return 'Open deadline';
    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function skillChips(value) {
    const skills = (value || 'Open skill set').split(',').map(skill => skill.trim()).filter(Boolean).slice(0, 4);
    return skills.map(skill => `<span>${escapeHtml(skill)}</span>`).join('');
}

async function loadTasks() {
    const data = await fetch('/api/tasks.php?action=list').then(r => r.json());
    const wrap = document.getElementById('tasksList');
    if (!data.success || !data.tasks.length) {
        wrap.innerHTML = '<div class="company-empty"><i class="fa-solid fa-folder-plus"></i><p>Post your first proof-of-work task.</p></div>';
        return;
    }
    wrap.innerHTML = data.tasks.map(task => `<article class="company-task-card">
        <div class="company-task-top">
            <div>
                <span>${escapeHtml(task.sub_count)} submission${Number(task.sub_count) === 1 ? '' : 's'}</span>
                <h3>${escapeHtml(task.title)}</h3>
            </div>
            ${badge(task.status)}
        </div>
        <p>${escapeHtml(task.description)}</p>
        <div class="company-chip-list">${skillChips(task.required_skills)}</div>
        <div class="company-task-meta">
            <span><i class="fa-solid fa-calendar-days"></i>${escapeHtml(formatCompanyDate(task.deadline))}</span>
            <span><i class="fa-solid fa-shield-check"></i>Proof-of-work brief</span>
        </div>
        <div class="company-task-actions">
            <button class="btn-soft min-h-0 px-3 py-2 text-sm" onclick="toggleTask(${task.id})"><i class="fa-solid fa-arrows-rotate"></i> Toggle status</button>
            <button class="btn-danger min-h-0 px-3 py-2 text-sm" onclick="deleteTask(${task.id})"><i class="fa-solid fa-trash"></i> Delete</button>
        </div>
    </article>`).join('');
}

async function loadSubmissions() {
    const data = await fetch('/api/submissions.php?action=list').then(r => r.json());
    const wrap = document.getElementById('submissionsList');
    if (!data.success || !data.submissions.length) {
        wrap.innerHTML = '<div class="company-empty"><i class="fa-solid fa-inbox"></i><p>Submissions from candidates will appear here.</p></div>';
        return;
    }
    wrap.innerHTML = data.submissions.map(item => `<article class="company-submission-card">
        <div class="company-submission-main">
            <div class="company-candidate-avatar">${escapeHtml((item.student_name || 'S').trim().charAt(0).toUpperCase())}</div>
            <div>
                <h3>${escapeHtml(item.student_name)} <span>${escapeHtml(item.title)}</span></h3>
                <p>${escapeHtml(item.student_email)} - ${escapeHtml(item.skills || 'No skills listed')}</p>
                <div class="company-submission-links">
                    <a href="${escapeHtml(item.submission_link)}" target="_blank" rel="noopener">Open work <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    ${item.portfolio_link ? `<a href="${escapeHtml(item.portfolio_link)}" target="_blank" rel="noopener">Portfolio <i class="fa-solid fa-arrow-up-right-from-square"></i></a>` : ''}
                </div>
            </div>
        </div>
        <div class="company-review-actions">
            <div>
                ${badge(item.status)}
            </div>
            <div class="company-status-buttons">
                ${['reviewed','shortlisted','rejected'].map(s => `<button class="btn-soft min-h-0 px-3 py-2 text-sm" onclick="setSubmissionStatus(${item.id}, '${s}')">${s}</button>`).join('')}
            </div>
        </div>
    </article>`).join('');
}

document.getElementById('taskForm').addEventListener('submit', async event => {
    event.preventDefault();
    const data = await postForm('/api/tasks.php', new FormData(event.target));
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        event.target.reset();
        closeModal('taskModal');
        loadTasks();
    }
});

async function toggleTask(id) {
    const fd = new FormData();
    fd.append('action', 'toggle_status');
    fd.append('id', id);
    const data = await postForm('/api/tasks.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    loadTasks();
}

async function deleteTask(id) {
    if (!confirm('Delete this task and all related submissions?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    const data = await postForm('/api/tasks.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    loadTasks();
    loadSubmissions();
}

async function setSubmissionStatus(id, status) {
    const fd = new FormData();
    fd.append('action', 'status');
    fd.append('id', id);
    fd.append('status', status);
    const data = await postForm('/api/submissions.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    loadSubmissions();
}

async function loadNotifications() {
    const data = await fetch('/api/notifications.php').then(r => r.json());
    const wrap = document.getElementById('notificationsList');
    wrap.innerHTML = data.success && data.notifications.length
        ? data.notifications.map(n => `<div class="company-notification ${n.is_read == 1 ? '' : 'unread'}">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <p>${escapeHtml(n.message)}</p>
                <span>${escapeHtml(n.created_at)}</span>
            </div>
        </div>`).join('')
        : '<div class="company-empty compact"><i class="fa-solid fa-bell-slash"></i><p>No notifications yet.</p></div>';
}

async function markNotificationsRead() {
    const fd = new FormData();
    fd.append('action', 'read');
    const data = await postForm('/api/notifications.php', fd);
    showToast(data.message, 'success');
    loadNotifications();
}

loadTasks();
loadSubmissions();
loadNotifications();
</script>
</body>
</html>
