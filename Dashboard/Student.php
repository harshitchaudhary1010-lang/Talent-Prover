<?php
require_once '../config/db.php';
require_once '../config/session.php';
require_once 'student_layout.php';
requireExactRole('student');

$pdo = getDB();
$profile = getStudentProfile($pdo);
$fields = [$profile['name'], $profile['email'], $profile['skills'], $profile['bio'], $profile['portfolio_link']];
$complete = (int)round((count(array_filter($fields)) / count($fields)) * 100);
$skillList = array_filter(array_map('trim', explode(',', $profile['skills'] ?: 'HTML, CSS, JavaScript')));
$avatar = !empty($profile['profile_image'])
    ? '<img src="' . htmlspecialchars($profile['profile_image']) . '" alt="Profile picture">'
    : htmlspecialchars(strtoupper(substr($profile['name'], 0, 1)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="student-portal">
<div class="student-layout">
    <?php renderStudentSidebar('overview'); ?>

    <main class="student-main">
        <header class="student-topbar">
            <button class="student-menu-btn lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="student-topbar-profile">
                <div class="student-topbar-avatar"><?= $avatar ?></div>
                <div>
                    <span class="student-eyebrow">Student portal</span>
                    <h1>Welcome back, <?= htmlspecialchars($profile['name']) ?>! <span class="student-wave">👋</span></h1>
                    <p>Track tasks, submit proof of work, and follow your review status.</p>
                </div>
            </div>
            <div class="student-top-actions">
                <button class="student-notification-btn" type="button" onclick="openModal('notificationModal')" aria-label="View notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span id="topNotificationCount">0</span>
                </button>
                <a class="student-action d-none d-md-inline-flex" href="/dashboard/tasks.php"><i class="fa-solid fa-magnifying-glass"></i> Find tasks</a>
            </div>
        </header>



        <section class="student-stat-grid">
            <div>
                <a class="student-stat-card student-stat-link emerald" href="/dashboard/tasks.php">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Open work</span>
                    <strong id="openTaskCount">--</strong>
                    <small>Tasks available to work on</small>
                    <em class="fa-regular fa-clipboard"></em>
                </a>
            </div>
            <div>
                <a class="student-stat-card student-stat-link amber" href="/dashboard/submissions.php">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Submissions</span>
                    <strong id="submissionCount">--</strong>
                    <small>Work submitted for review</small>
                    <em class="fa-regular fa-file-lines"></em>
                </a>
            </div>
            <div>
                <button class="student-stat-card student-stat-link blue" type="button" onclick="openModal('notificationModal')">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                    <strong id="notificationCount">--</strong>
                    <small>Unread notifications</small>
                    <em class="fa-regular fa-envelope-open"></em>
                </button>
            </div>
        </section>

        <section class="student-dashboard-grid">
            <div class="student-panel" id="tasks">
                <div class="student-panel-head">
                    <div>
                        <span>Handpicked tasks based on your skills</span>
                        <h2>Recommended Tasks</h2>
                    </div>
                    <a class="student-view-all" href="/dashboard/tasks.php">View all</a>
                </div>
                <div id="tasksList" class="student-recommended-list"></div>
            </div>
            <div class="student-dashboard-rail">
                <section id="submissions" class="student-panel">
                    <div class="student-panel-head">
                        <div>
                            <span>Latest review activity</span>
                            <h2>Recent Submissions</h2>
                        </div>
                        <a class="student-view-all" href="/dashboard/submissions.php">View all</a>
                    </div>
                    <div id="submissionsList" class="student-submission-list compact"></div>
                </section>

            </div>
        </section>
    </main>
</div>

<div id="notificationModal" class="modal-backdrop student-notification-backdrop" data-modal-backdrop>
    <div class="modal-panel student-notification-modal">
        <div class="student-notification-modal-head">
            <div>
                <span class="student-eyebrow">Notifications</span>
                <h2>Latest updates</h2>
            </div>
            <button class="student-modal-close" type="button" onclick="closeModal('notificationModal')" aria-label="Close notifications">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="student-notification-modal-tools">
            <span id="notificationPopupSummary">No unread notifications</span>
            <button type="button" onclick="markNotificationsRead()">Mark all read</button>
        </div>
        <div id="notificationPopupList" class="student-notification-list popup"></div>
    </div>
</div>

<div id="submitModal" class="modal-backdrop" data-modal-backdrop>
    <div class="modal-panel p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-black">Submit completed work</h2>
            <button class="btn-soft min-h-0 px-3 py-2" onclick="closeModal('submitModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="submissionForm" class="grid gap-4">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="task_id" id="submissionTaskId">
            <div class="form-group">
                <label class="form-label">GitHub, Google Drive, or live demo link</label>
                <input class="form-input" type="url" name="submission_link" required placeholder="https://github.com/you/project">
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea class="form-textarea" name="message" placeholder="Briefly explain your solution and decisions."></textarea>
            </div>
            <button class="btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Send submission</button>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const mySkills = <?= json_encode(strtolower($profile['skills'] ?? '')) ?>;

function matchesSkills(task) {
    const skills = (task.required_skills || '').toLowerCase().split(',').map(s => s.trim()).filter(Boolean);
    return skills.some(skill => mySkills.includes(skill));
}

function formatDeadline(deadline) {
    if (!deadline) return 'Open deadline';
    const date = new Date(`${deadline}T00:00:00`);
    if (Number.isNaN(date.getTime())) return deadline;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.ceil((date - today) / 86400000);
    const label = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    if (diffDays < 0) return `${label} - expired`;
    if (diffDays === 0) return `${label} - due today`;
    if (diffDays === 1) return `${label} - 1 day left`;
    return `${label} - ${diffDays} days left`;
}

function skillChips(value) {
    const skills = (value || 'Open skill set').split(',').map(skill => skill.trim()).filter(Boolean).slice(0, 4);
    return skills.map(skill => `<span>${escapeHtml(skill)}</span>`).join('');
}

function compactSkillChips(value) {
    const skills = (value || 'Open skill set').split(',').map(skill => skill.trim()).filter(Boolean).slice(0, 3);
    return skills.map(skill => `<span>${escapeHtml(skill)}</span>`).join('');
}

async function loadTasks() {
    const data = await fetch('/api/tasks.php?action=list').then(r => r.json());
    const wrap = document.getElementById('tasksList');
    if (!data.success || !data.tasks.length) {
        document.getElementById('openTaskCount').textContent = 0;
        wrap.innerHTML = '<div class="student-empty"><i class="fa-solid fa-folder-open"></i><p>No active tasks yet.</p></div>';
        return;
    }
    document.getElementById('openTaskCount').textContent = data.tasks.length;
    wrap.innerHTML = data.tasks.slice(0, 3).map(task => {
        const recommended = matchesSkills(task);
        const companyInitial = escapeHtml((task.company_name || 'C').trim().charAt(0).toUpperCase());
        return `<article class="student-recommended-item">
            <div class="student-code-icon">${companyInitial}</div>
            <div>
                <h3>${escapeHtml(task.title)}</h3>
                <p>${escapeHtml(task.company_name || 'Verified company')}</p>
                <div class="student-compact-skills">${compactSkillChips(task.required_skills)}</div>
            </div>
            <div class="student-recommended-meta">
                <span>${recommended ? 'Matched' : 'Open'}</span>
                <small>${escapeHtml(formatDeadline(task.deadline))}</small>
            </div>
            <a href="/dashboard/task.php?id=${task.id}" aria-label="View task"><i class="fa-solid fa-chevron-right"></i></a>
        </article>`;
    }).join('');
}

async function loadSubmissions() {
    const data = await fetch('/api/submissions.php?action=list').then(r => r.json());
    const wrap = document.getElementById('submissionsList');
    if (!data.success || !data.submissions.length) {
        document.getElementById('submissionCount').textContent = 0;
        wrap.innerHTML = '<div class="student-empty compact"><i class="fa-solid fa-inbox"></i><p>Your submitted work will appear here.</p></div>';
        return;
    }
    document.getElementById('submissionCount').textContent = data.submissions.length;
    wrap.innerHTML = data.submissions.slice(0, 3).map(item => `<article class="student-submission-item">
        <div>
            <div class="student-submission-title">
                <h3>${escapeHtml(item.title)}</h3>
            </div>
            <p>Submitted on ${escapeHtml(item.submitted_at)}</p>
        </div>
        <div class="student-submission-actions">
            <span class="student-badge ${escapeHtml(item.status)}">${escapeHtml(item.status)}</span>
        </div>
    </article>`).join('');
}

async function loadNotifications() {
    const data = await fetch('/api/notifications.php').then(r => r.json());
    const popupWrap = document.getElementById('notificationPopupList');
    const popupSummary = document.getElementById('notificationPopupSummary');
    if (!data.success || !data.notifications.length) {
        document.getElementById('notificationCount').textContent = 0;
        document.getElementById('topNotificationCount').textContent = 0;
        const emptyState = '<div class="student-empty compact"><i class="fa-solid fa-bell-slash"></i><p>No notifications yet.</p></div>';
        popupWrap.innerHTML = emptyState;
        popupSummary.textContent = 'No unread notifications';
        return;
    }
    document.getElementById('notificationCount').textContent = data.notifications.length;
    const unreadCount = data.notifications.filter(n => Number(n.is_read) !== 1).length;
    document.getElementById('topNotificationCount').textContent = unreadCount;
    popupSummary.textContent = unreadCount ? `${unreadCount} unread notification${unreadCount === 1 ? '' : 's'}` : 'All notifications are read';
    const notificationItems = data.notifications.map(n => {
        const tone = String(n.message || '').toLowerCase().includes('rejected') ? 'rejected' : '';
        return `<div class="student-notification ${n.is_read == 1 ? '' : 'unread'} ${tone}">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <p>${escapeHtml(n.message)}</p>
            <span>${escapeHtml(n.created_at)}</span>
        </div>
    </div>`;
    }).join('');
    popupWrap.innerHTML = notificationItems;
}

function beginSubmission(id) {
    document.getElementById('submissionTaskId').value = id;
    openModal('submitModal');
}

document.getElementById('submissionForm').addEventListener('submit', async event => {
    event.preventDefault();
    const data = await postForm('/api/submissions.php', new FormData(event.target));
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        event.target.reset();
        closeModal('submitModal');
        loadSubmissions();
    }
});

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
