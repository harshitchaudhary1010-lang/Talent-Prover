<?php
require_once '../config/db.php';
require_once '../config/session.php';
require_once 'student_layout.php';
requireExactRole('student');

$pdo = getDB();
$profile = getStudentProfile($pdo);
?>
<?php studentPageHead('Available Tasks'); ?>
<body class="student-portal">
<div class="student-layout">
    <?php renderStudentSidebar('tasks'); ?>
    <main class="student-main">
        <header class="student-topbar">
            <button class="student-menu-btn lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div>
                <span class="student-eyebrow">Recommended work</span>
                <h1>Available Tasks</h1>
                <p>Browse active proof-of-work tasks and open the full brief before submitting.</p>
            </div>
            <a class="student-action d-none d-md-inline-flex" href="/dashboard/profile.php"><i class="fa-solid fa-user-gear"></i> Profile</a>
        </header>

        <section class="student-page-section">
            <div class="student-section-head">
                <div>
                    <span>Open assignments</span>
                    <h2>Choose a task</h2>
                </div>
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div id="tasksList" class="row g-4"></div>
        </section>
    </main>
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

async function loadTasks() {
    const data = await fetch('/api/tasks.php?action=list').then(r => r.json());
    const wrap = document.getElementById('tasksList');
    if (!data.success || !data.tasks.length) {
        wrap.innerHTML = '<div class="col-12"><div class="student-empty"><i class="fa-solid fa-folder-open"></i><p>No active tasks yet.</p></div></div>';
        return;
    }
    wrap.innerHTML = data.tasks.map(task => {
        const recommended = matchesSkills(task);
        const companyInitial = escapeHtml((task.company_name || 'C').trim().charAt(0).toUpperCase());
        return `<div class="col-md-6 col-lg-4"><article class="student-task-card">
            <div class="student-task-top">
                <div class="student-task-company">
                    <div class="student-company-logo">${companyInitial}</div>
                    <div>
                        <p>${escapeHtml(task.company_name || 'Verified company')}</p>
                        <h3><a href="/dashboard/task.php?id=${task.id}">${escapeHtml(task.title)}</a></h3>
                    </div>
                </div>
                ${recommended ? '<span class="student-badge success">Recommended</span>' : `<span class="student-badge">${escapeHtml(task.status)}</span>`}
            </div>
            <p class="student-task-desc">${escapeHtml(task.description)}</p>
            <div class="student-task-skills">${skillChips(task.required_skills)}</div>
            <div class="student-task-meta">
                <span><i class="fa-solid fa-location-dot"></i>Remote proof-of-work</span>
                <span><i class="fa-solid fa-calendar-days"></i>${escapeHtml(formatDeadline(task.deadline))}</span>
                <span><i class="fa-solid fa-shield-check"></i>Verified task brief</span>
            </div>
            <div class="student-task-footer">
                <span><i class="fa-solid fa-clock"></i> Review after submission</span>
                <div class="student-task-actions">
                    <a class="student-detail-btn" href="/dashboard/task.php?id=${task.id}">View details</a>
                    <button class="student-submit-btn" onclick="beginSubmission(${task.id})"><i class="fa-solid fa-paper-plane"></i> Submit</button>
                </div>
            </div>
        </article></div>`;
    }).join('');
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
    }
});

loadTasks();
</script>
</body>
</html>
