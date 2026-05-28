<?php
require_once '../config/db.php';
require_once '../config/session.php';
require_once 'student_layout.php';
requireExactRole('student');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT s.*, t.title, cp.company_name
    FROM submissions s
    JOIN tasks t ON t.id = s.task_id
    JOIN company_profiles cp ON cp.user_id = t.company_id
    WHERE s.student_id = ?
    ORDER BY s.submitted_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();
?>
<?php studentPageHead('My Submissions'); ?>
<body class="student-portal">
<div class="student-layout">
    <?php renderStudentSidebar('submissions'); ?>
    <main class="student-main">
        <header class="student-topbar">
            <button class="student-menu-btn lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div>
                <span class="student-eyebrow">Application tracker</span>
                <h1>My Submissions</h1>
                <p>Track every task you submitted and follow the review status.</p>
            </div>
            <a class="student-action d-none d-md-inline-flex" href="/dashboard/tasks.php"><i class="fa-solid fa-briefcase"></i> Browse tasks</a>
        </header>

        <section class="student-panel student-page-section">
            <div class="student-panel-head">
                <div>
                    <span>Submitted work</span>
                    <h2>Review status</h2>
                </div>
            </div>
            <div class="student-submission-list">
                <?php if (!$submissions): ?>
                    <div class="student-empty"><i class="fa-solid fa-inbox"></i><p>Your submitted work will appear here.</p></div>
                <?php endif; ?>
                <?php foreach ($submissions as $item): ?>
                    <article class="student-submission-item">
                        <div>
                            <div class="student-submission-title">
                                <i class="fa-solid fa-file-lines"></i>
                                <h3><?= htmlspecialchars($item['title']) ?></h3>
                            </div>
                            <p><?= htmlspecialchars($item['company_name']) ?> - <?= htmlspecialchars($item['submitted_at']) ?></p>
                        </div>
                        <div class="student-submission-actions">
                            <span class="student-badge <?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span>
                            <a href="<?= htmlspecialchars($item['submission_link']) ?>" target="_blank" rel="noopener">Open <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>