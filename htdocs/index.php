<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

function homepageTasks() {
    try {
        bootstrapDatabase();
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, dbOptions());
        $stmt = $pdo->query("
            SELECT t.id, t.title, t.required_skills, t.deadline, cp.company_name
            FROM tasks t
            JOIN company_profiles cp ON cp.user_id = t.company_id
            JOIN users u ON u.id = t.company_id
            WHERE t.status = 'active' 
            AND u.status = 'active'
            AND (t.deadline IS NULL OR t.deadline >= CURDATE())
            ORDER BY t.created_at DESC
            LIMIT 3
        ");
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function taskDeadlineLabel($deadline) {
    if (!$deadline) {
        return 'Open deadline';
    }

    $today = new DateTimeImmutable('today');
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $deadline);
    if (!$date) {
        return 'Open deadline';
    }

    $days = (int)$today->diff($date)->format('%r%a');
    if ($days < 0) {
        return 'Deadline passed';
    }
    if ($days === 0) {
        return 'Due today';
    }
    if ($days === 1) {
        return '1 day left';
    }
    return $days . ' days left';
}

$homepageTasks = homepageTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TalentProve - Task-Based Hiring Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="page-bg text-white">
<div class="pattern-layer" aria-hidden="true"></div>

<header class="site-header sticky top-0 z-50 mx-auto flex max-w-7xl items-center justify-between px-5 py-2">
    <a href="/" class="brand-mark">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
    </a>
    <nav id="siteNav" class="site-nav hidden items-center gap-7 text-sm font-bold text-slate-300 md:flex">
        <a href="#features" class="hover:text-white">Features</a>
        <a href="/tasks.php" class="hover:text-white">Tasks</a>
        <a href="#how" class="hover:text-white">How it works</a>
        <a href="#standards" class="hover:text-white">Standards</a>
        <a href="#team" class="hover:text-white">Team</a>
        <a href="/auth/login.php" class="md:hidden">Login</a>
        <a href="/auth/register.php" class="md:hidden">Create account</a>
    </nav>
    <div class="hidden items-center gap-3 md:flex">
        <?php if (isLoggedIn()): ?>
            <a class="btn-secondary" href="/Dashboard/<?= htmlspecialchars($_SESSION['role']) ?>.php">Dashboard</a>
        <?php else: ?>
            <a class="nav-login" href="/auth/login.php">Login</a>
        <?php endif; ?>
        <a class="btn-primary" href="/auth/register.php">Create account</a>
    </div>
    <button class="nav-toggle md:hidden" type="button" onclick="toggleTopNav()" aria-label="Open navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<div id="mobileNav" class="mobile-nav mx-auto hidden max-w-7xl px-5 md:hidden">
    <a href="#features">Features</a>
    <a href="/tasks.php">Tasks</a>
    <a href="#how">How it works</a>
    <a href="#standards">Standards</a>
    <a href="#team">Team</a>
    <?php if (isLoggedIn()): ?>
        <a href="/Dashboard/<?= htmlspecialchars($_SESSION['role']) ?>.php">Dashboard</a>
    <?php else: ?>
        <a href="/auth/login.php">Login</a>
    <?php endif; ?>
    <a href="/auth/register.php">Create account</a>
</div>

<main class="relative z-10">
    <section class="hero-section hero-rect grid min-h-[70vh] items-center gap-10 px-5 pb-12 pt-10 lg:grid-cols-[1fr_.9fr]">
        <div class="max-w-4xl fade-in reveal">
            <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-900/10 bg-emerald-50 px-4 py-2 text-sm font-extrabold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                Talent evaluation through real work
            </p>
            <h1 class="hero-title max-w-4xl text-5xl font-black leading-tight md:text-7xl">
                Prove Your Skills. Get Hired by Real Companies.
            </h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-600">
                TalentProve helps teams evaluate candidates through focused work samples, not long resume screens. Students complete real tasks, companies review the evidence, and decisions move faster.
            </p>
            <div class="mt-6 grid max-w-2xl gap-3 sm:grid-cols-3">
                <div class="hero-proof-pill"><span>01</span> Task brief</div>
                <div class="hero-proof-pill"><span>02</span> Work submission</div>
                <div class="hero-proof-pill"><span>03</span> Review decision</div>
            </div>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="/auth/register.php" class="btn-primary"><i class="fa-solid fa-graduation-cap"></i> Join as student</a>
                <a href="/auth/login.php?role=company" class="btn-secondary"><i class="fa-solid fa-building"></i> Post a company task</a>
            </div>
            <div class="job-search-panel mt-8">
                <div class="job-search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Job title, skill, or company">
                </div>
                <div class="job-search-field">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" placeholder="Location or remote">
                </div>
                <a href="/tasks.php" class="job-search-btn">Search tasks</a>
            </div>
            <div class="quick-tags mt-4">
                <span>Popular:</span>
                <a href="/tasks.php?q=Frontend">Frontend</a>
                <a href="/tasks.php?q=PHP">PHP</a>
                <a href="/tasks.php?q=UI%20Design">UI Design</a>
                <a href="/tasks.php?q=Internship">Internship</a>
            </div>
            <div class="mt-8 flex flex-wrap items-center gap-4 text-sm font-bold text-slate-300">
                <span class="flex items-center gap-2 text-slate-600"><i class="fa-solid fa-shield-check text-emerald-600"></i> Verified companies</span>
                <span class="flex items-center gap-2 text-slate-600"><i class="fa-solid fa-list-check text-sky-600"></i> Task-based applications</span>
                <span class="flex items-center gap-2 text-slate-600"><i class="fa-solid fa-user-check text-teal-600"></i> Better matching</span>
            </div>
        </div>
        <div class="hero-rect-image fade-in reveal">
            <img src="/assets/images/hero-workspace.png" alt="TalentProve hiring task workspace">
        </div>
    </section>

    <section class="relative z-10 px-5 pb-12">
        <div class="trust-strip mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
            <div>
                <span>Verified listings</span>
                <strong>Task opportunities from real companies</strong>
            </div>
            <div>
                <span>Proof-based hiring</span>
                <strong>Review submissions before interviews</strong>
            </div>
            <div>
                <span>Career growth</span>
                <strong>Build a profile around real work</strong>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 text-slate-950">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 lg:grid-cols-[.95fr_1.05fr]">
            <div class="image-stack reveal">
                <img class="stack-main" src="/assets/images/indian-employees.jpg" alt="Indian employees working on a laptop">
            </div>
            <div class="reveal">
                <p class="section-kicker">Work samples first</p>
                <h2 class="mt-2 text-3xl font-black md:text-5xl">Review the work before scheduling the interview.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-600">Candidates submit links, notes, and demos in one place. Companies can compare practical outcomes and keep every review status easy to understand.</p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-900/10">
                        <i class="fa-solid fa-link text-purple-600"></i>
                        <p class="mt-3 font-black">GitHub, Drive, and demo links</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-900/10">
                        <i class="fa-solid fa-ranking-star text-sky-600"></i>
                        <p class="mt-3 font-black">Pending, reviewed, shortlisted</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="job-market-section py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div class="max-w-2xl reveal">
                    <p class="section-kicker">Explore work samples</p>
                    <h2 class="mt-2 text-3xl font-black md:text-5xl">Find tasks that match real hiring needs.</h2>
                    <p class="mt-4 leading-7 text-slate-600">TalentProve works like a job portal, but every opportunity is attached to a practical assignment companies can review.</p>
                </div>
                <a class="btn-secondary w-max reveal" href="/tasks.php">Browse tasks</a>
            </div>
            <div class="grid gap-5 lg:grid-cols-3">
                <?php if ($homepageTasks): ?>
                <?php foreach ($homepageTasks as $task): ?>
                    <article class="job-card reveal">
                        <div class="job-card-body">
                            <span class="badge badge-active">Active task</span>
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                            <p class="company-line"><i class="fa-solid fa-building"></i><?= htmlspecialchars($task['company_name']) ?></p>
                            <p class="skills-line"><i class="fa-solid fa-code"></i><?= htmlspecialchars($task['required_skills'] ?: 'Open skills') ?></p>
                            <div class="job-card-footer">
                                <span><i class="fa-solid fa-clock"></i><?= htmlspecialchars(taskDeadlineLabel($task['deadline'])) ?></span>
                                <a href="/tasks.php">View task</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php else: ?>
                    <article class="job-card reveal lg:col-span-3">
                        <div class="job-card-body">
                            <span class="badge badge-pending">No live tasks</span>
                            <h3>Company-posted tasks will appear here.</h3>
                            <p class="company-line"><i class="fa-solid fa-building"></i>Ask a company account to post a task from the dashboard.</p>
                            <p class="skills-line"><i class="fa-solid fa-code"></i>Only active tasks from companies are shown in this section.</p>
                            <div class="job-card-footer">
                                <span><i class="fa-solid fa-clock"></i>Waiting for posts</span>
                                <a href="/auth/register.php?role=company">Post task</a>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="portal-strip py-12 text-slate-950">
        <div class="mx-auto grid max-w-7xl gap-5 px-5 md:grid-cols-4">
            <?php foreach ([['10+', 'candidate profiles'], ['120+', 'company tasks'], ['35+', 'skill categories'], ['24h', 'review updates']] as $item): ?>
                <div class="portal-stat reveal">
                    <strong><?= $item[0] ?></strong>
                    <span><?= $item[1] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="category-section bg-white py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <div class="mb-10 max-w-2xl reveal">
                <p class="section-kicker">Popular skill tracks</p>
                <h2 class="mt-2 text-3xl font-black md:text-4xl">Opportunities organized by skill.</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <?php
                $categories = [
                    ['fa-code', 'Web Development', '42 tasks'],
                    ['fa-palette', 'UI / UX Design', '28 tasks'],
                    ['fa-database', 'Backend & Database', '31 tasks'],
                    ['fa-bullhorn', 'Marketing & Content', '18 tasks'],
                ];
                foreach ($categories as $category): ?>
                    <article class="category-card reveal">
                        <i class="fa-solid <?= $category[0] ?>"></i>
                        <h3><?= $category[1] ?></h3>
                        <p><?= $category[2] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="audience-section bg-slate-50 py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <div class="mb-10 max-w-3xl reveal">
                <p class="section-kicker">Designed for the whole workflow</p>
                <h2 class="mt-2 text-3xl font-black md:text-4xl">Each role gets the tools they actually need.</h2>
            </div>
            <div class="grid gap-5 lg:grid-cols-2">
                <article class="audience-card reveal">
                    <div class="audience-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <h3>Student / Worker</h3>
                    <p>Create a profile, choose skills, browse tasks, submit work links, and track every result without confusion.</p>
                    <ul>
                        <li>Skill-based task discovery</li>
                        <li>Portfolio and demo link support</li>
                        <li>Submission status tracking</li>
                    </ul>
                </article>
                <article class="audience-card reveal">
                    <div class="audience-icon"><i class="fa-solid fa-building"></i></div>
                    <h3>Company</h3>
                    <p>Post realistic assignments, review candidate work, shortlist strong submissions, and contact the right people.</p>
                    <ul>
                        <li>Task posting dashboard</li>
                        <li>Submission review panel</li>
                        <li>Shortlist and hiring actions</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section id="features" class="bg-white py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <div class="mb-10 max-w-2xl reveal">
                <p class="section-kicker">Why TalentProve</p>
                <h2 class="mt-2 text-3xl font-black md:text-4xl">A quieter, clearer way to evaluate talent.</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <?php
                $features = [
                    ['fa-briefcase', 'Practical task briefs', 'Companies publish assignments with required skills, deadlines, and expected deliverables.'],
                    ['fa-code', 'Evidence-based profiles', 'Students apply with work links, a short explanation, and skills that match the task.'],
                    ['fa-chart-line', 'Clear review flow', 'Companies see submitted work, review status, and shortlisted candidates in one organized place.'],
                ];
                foreach ($features as $item): ?>
                    <article class="white-card feature-card p-6 reveal">
                        <div class="logo-icon mb-5"><i class="fa-solid <?= $item[0] ?>"></i></div>
                        <h3 class="text-xl font-black"><?= $item[1] ?></h3>
                        <p class="mt-3 leading-7 text-slate-600"><?= $item[2] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="how" class="bg-slate-50 py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <h2 class="text-3xl font-black md:text-4xl reveal">How the workflow feels</h2>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                <?php
                $steps = [
                    ['1', 'Create a focused task', 'Define the work, skills, deadline, and what a good submission should include.'],
                    ['2', 'Submit a useful proof', 'Students share a GitHub, Drive, or live demo link with a short explanation.'],
                    ['3', 'Review with context', 'Companies update status, shortlist strong work, and contact the right candidates.'],
                ];
                foreach ($steps as $step): ?>
                    <article class="white-card step-card p-6 reveal">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-950 font-black text-white"><?= $step[0] ?></span>
                        <h3 class="mt-5 text-xl font-black"><?= $step[1] ?></h3>
                        <p class="mt-3 leading-7 text-slate-600"><?= $step[2] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="standards" class="standards-section py-24 text-slate-950">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 lg:grid-cols-[.9fr_1.1fr]">
            <div class="reveal">
                <p class="section-kicker">Company-grade workflow</p>
                <h2 class="mt-2 text-3xl font-black md:text-5xl">Detailed enough for hiring work, simple enough to use daily.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-600">Every task, submission, review, and shortlist status is organized so companies can make decisions without losing context, and students can see where their work stands.</p>
                <div class="mt-8 grid gap-4">
                    <div class="standard-row"><i class="fa-solid fa-clipboard-check"></i><span>Clear task briefs with required skills, deadlines, and deliverables.</span></div>
                    <div class="standard-row"><i class="fa-solid fa-user-shield"></i><span>Separate dashboards for students and companies keep each workflow focused.</span></div>
                    <div class="standard-row"><i class="fa-solid fa-bell"></i><span>Notifications keep every review movement visible and accountable.</span></div>
                </div>
            </div>
            <div class="operations-panel reveal">
                <div class="ops-header">
                    <div>
                        <p class="text-sm font-black text-slate-500">Review pipeline</p>
                        <h3 class="text-2xl font-black">Candidate quality board</h3>
                    </div>
                    <span class="badge badge-active">Live</span>
                </div>
                <div class="ops-list">
                    <div class="ops-item">
                        <span class="ops-rank">01</span>
                        <div>
                            <p class="font-black">Responsive dashboard task</p>
                            <p class="text-sm text-slate-500">HTML, CSS, JavaScript - 16 submissions</p>
                        </div>
                        <span class="badge badge-shortlisted">Shortlist</span>
                    </div>
                    <div class="ops-item">
                        <span class="ops-rank">02</span>
                        <div>
                            <p class="font-black">PHP validation flow</p>
                            <p class="text-sm text-slate-500">PHP, PDO, MySQL - 9 submissions</p>
                        </div>
                        <span class="badge badge-reviewed">Review</span>
                    </div>
                    <div class="ops-item">
                        <span class="ops-rank">03</span>
                        <div>
                            <p class="font-black">Portfolio proof audit</p>
                            <p class="text-sm text-slate-500">UI, content, demo links - 24 submissions</p>
                        </div>
                        <span class="badge badge-pending">Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="bg-white py-20 text-slate-950">
        <div class="mx-auto grid max-w-7xl gap-5 px-5 md:grid-cols-4">
            <?php foreach ([['Task briefs', 'Structured by skills'], ['Submissions', 'Tracked by status'], ['Shortlists', 'Ready for review'], ['Company view', 'One review workspace']] as $stat): ?>
                <div class="metric-card text-center reveal">
                    <p class="stat-number text-2xl font-black text-slate-950"><?= $stat[0] ?></p>
                    <p class="mt-2 font-bold text-slate-500"><?= $stat[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="team" class="team-section bg-slate-50 py-24 text-slate-950">
        <div class="mx-auto max-w-7xl px-5">
            <div class="mx-auto mb-12 max-w-2xl text-center reveal">
                <p class="section-kicker">The team</p>
                <h2 class="mt-2 text-3xl font-black md:text-5xl">The people shaping TalentProve.</h2>
                <p class="mt-4 leading-7 text-slate-600">The platform is designed around practical hiring, clear product decisions, and a better way for students to show what they can actually build.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <?php
                $team = [
                    ['Garima Chaudhary', 'UI/UX Designer', '/assets/images/Founder/garima-bgremoved.png'],
                    ['Harshit Chaudhary', 'Frontend Developer', '/assets/images/Founder/harshit-bgremoved.png'],
                    ['Siddharth Lama', 'Backend Developer', '/assets/images/Founder/siddharth-bgremoved.png'],
                ];
                foreach ($team as $member): ?>
                    <article class="team-card reveal">
                        <div class="team-photo">
                            <img src="<?= htmlspecialchars($member[2]) ?>" alt="<?= htmlspecialchars($member[0]) ?>">
                        </div>
                        <div class="p-6">
                            <p class="text-sm font-black uppercase tracking-[.14em] text-purple-600"><?= htmlspecialchars($member[1]) ?></p>
                            <h3 class="mt-2 text-2xl font-black"><?= htmlspecialchars($member[0]) ?></h3>
                            <div class="mt-5 flex gap-3">
                                <span class="team-social"><i class="fa-brands fa-linkedin-in"></i></span>
                                <span class="team-social"><i class="fa-brands fa-github"></i></span>
                                <span class="team-social"><i class="fa-solid fa-envelope"></i></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="px-5 py-20">
        <div class="cta-panel mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-white/15 bg-white/10 p-8 text-center shadow-2xl backdrop-blur md:p-12 reveal">
            <h2 class="text-3xl font-black md:text-5xl">Start with one real task.</h2>
            <p class="mx-auto mt-4 max-w-2xl text-slate-300">Create an account, publish or complete a focused assignment, and keep the full review process in one professional workspace.</p>
            <a href="/auth/register.php" class="btn-primary mt-8">Create your workspace</a>
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
            <a href="#features">Features</a>
            <a href="#how">Workflow</a>
            <a href="#standards">Standards</a>
        </div>
        <div>
            <h3>Accounts</h3>
            <a href="/auth/register.php">Create account</a>
            <a href="/auth/login.php">Login</a>
            <a href="#team">Team</a>
        </div>
    </div>
    <div class="mx-auto mt-8 max-w-7xl border-t border-white/10 pt-6">
        Built by Team Startup.
    </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>
