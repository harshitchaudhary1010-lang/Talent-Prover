<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('student');

$taskId = (int)($_GET['id'] ?? 0);
$pdo = getDB();

$stmt = $pdo->prepare("SELECT t.*, cp.company_name, cp.industry, cp.website
    FROM tasks t
    JOIN company_profiles cp ON cp.user_id = t.company_id
    WHERE t.id = ? AND t.status = 'active'
    LIMIT 1");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(404);
}

$alreadySubmitted = false;
if ($task) {
    $stmt = $pdo->prepare("SELECT id, status, submitted_at, submission_link FROM submissions WHERE task_id = ? AND student_id = ? LIMIT 1");
    $stmt->execute([$taskId, $_SESSION['user_id']]);
    $alreadySubmitted = $stmt->fetch();
}

$skills = $task ? array_filter(array_map('trim', explode(',', $task['required_skills'] ?? ''))) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $task ? htmlspecialchars($task['title']) : 'Task not found' ?> - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="student-portal">
<main class="student-task-page">
    <a class="student-back-link" href="/dashboard/tasks.php"><i class="fa-solid fa-arrow-left"></i> Back to tasks</a>

    <?php if (!$task): ?>
        <section class="student-panel mt-4">
            <div class="student-empty">
                <i class="fa-solid fa-folder-open"></i>
                <p>This task is not available.</p>
            </div>
        </section>
    <?php else: ?>
        <section class="task-detail-hero">
            <div>
                <p class="student-eyebrow"><?= htmlspecialchars($task['company_name']) ?></p>
                <h1><?= htmlspecialchars($task['title']) ?></h1>
                <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                <div class="student-task-skills detail-skills">
                    <?php foreach ($skills as $skill): ?>
                        <span><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                    <?php if (!$skills): ?>
                        <span>Open skill set</span>
                    <?php endif; ?>
                </div>
            </div>
            <aside class="task-detail-company">
                <div class="student-company-logo"><?= htmlspecialchars(strtoupper(substr($task['company_name'], 0, 1))) ?></div>
                <h2><?= htmlspecialchars($task['company_name']) ?></h2>
                <p><?= htmlspecialchars($task['industry'] ?: 'Industry not listed') ?></p>
                <?php if (!empty($task['website'])): ?>
                    <a href="<?= htmlspecialchars($task['website']) ?>" target="_blank" rel="noopener">Company website <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <?php endif; ?>
            </aside>
        </section>

        <section class="task-detail-grid">
            <article class="student-panel">
                <div class="student-panel-head">
                    <div>
                        <span>Task brief</span>
                        <h2>What to submit</h2>
                    </div>
                </div>
                <div class="task-detail-list">
                    <div><i class="fa-solid fa-link"></i><span>Share a GitHub, Google Drive, portfolio, or live demo URL.</span></div>
                    <div><i class="fa-solid fa-note-sticky"></i><span>Add a short note explaining what you built and your decisions.</span></div>
                    <div><i class="fa-solid fa-calendar-days"></i><span>Deadline: <?= htmlspecialchars($task['deadline'] ?: 'Open deadline') ?></span></div>
                    <div><i class="fa-solid fa-shield-check"></i><span>Company will review your work inside TalentProve.</span></div>
                </div>
            </article>

            <article class="student-panel">
                <div class="student-panel-head">
                    <div>
                        <span>Submission</span>
                        <h2><?= $alreadySubmitted ? 'Already submitted' : 'Send your work' ?></h2>
                    </div>
                </div>
                <?php if ($alreadySubmitted): ?>
                    <div class="task-submitted-box">
                        <span class="student-badge <?= htmlspecialchars($alreadySubmitted['status']) ?>"><?= htmlspecialchars($alreadySubmitted['status']) ?></span>
                        <p>Submitted on <?= htmlspecialchars($alreadySubmitted['submitted_at']) ?></p>
                        <a href="<?= htmlspecialchars($alreadySubmitted['submission_link']) ?>" target="_blank" rel="noopener">Open submission <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </div>
                <?php else: ?>
                    <form id="taskDetailSubmissionForm" class="grid gap-4">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                        <div class="form-group">
                            <label class="form-label">Work link</label>
                            <input class="form-input" type="url" name="submission_link" required placeholder="https://github.com/you/project">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea class="form-textarea" name="message" placeholder="Briefly explain your approach and final result."></textarea>
                        </div>
                        <button class="student-submit-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Submit work</button>
                    </form>
                <?php endif; ?>
            </article>
        </section>
    <?php endif; ?>
</main>

<script src="/assets/js/main.js"></script>
<script>
const detailForm = document.getElementById('taskDetailSubmissionForm');
if (detailForm) {
    detailForm.addEventListener('submit', async event => {
        event.preventDefault();
        const data = await postForm('/api/submissions.php', new FormData(event.target));
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => window.location.reload(), 900);
    });
}
</script>
</body>
</html>
