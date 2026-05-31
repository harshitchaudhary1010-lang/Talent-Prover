<?php
require_once '../config/db.php';
require_once '../config/session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php'); exit;
}
$pdo = getDB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="adm-shell">

<aside id="sidebar" class="adm-sidebar">
  <a class="adm-brand" href="/">
    <img src="/assets/images/logo.jpeg" alt="TalentProve">
    <span>TalentProve</span>
  </a>
  <nav class="adm-nav">
    <a class="adm-link active" href="#overview" onclick="showSection('overview',this)"><i class="fa-solid fa-chart-pie"></i><span>Overview</span></a>
    <a class="adm-link" href="#users" onclick="showSection('users',this)"><i class="fa-solid fa-users"></i><span>Users</span></a>
    <a class="adm-link" href="#tasks" onclick="showSection('tasks',this)"><i class="fa-solid fa-briefcase"></i><span>Tasks</span></a>
    <a class="adm-link" href="#submissions" onclick="showSection('submissions',this)"><i class="fa-solid fa-paper-plane"></i><span>Submissions</span></a>
  </nav>
  <a class="adm-logout" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
</aside>

<main class="adm-main">

  <!-- TOP BAR -->
  <header class="adm-topbar">
    <button class="adm-menu-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    <div>
      <p class="adm-eyebrow">Admin Control Center</p>
      <h1 class="adm-title">Platform Dashboard</h1>
    </div>
    <div class="adm-topbar-right">
      <span id="adm-date" class="adm-date-badge"></span>
      <button class="adm-refresh-btn" onclick="loadAll()" title="Refresh data"><i class="fa-solid fa-rotate"></i></button>
    </div>
  </header>

  <!-- OVERVIEW SECTION -->
  <section id="sec-overview" class="adm-section">

    <!-- KPI CARDS -->
    <div class="adm-kpi-grid" id="kpiGrid">
      <div class="adm-kpi adm-kpi-purple"><div class="adm-kpi-icon"><i class="fa-solid fa-graduation-cap"></i></div><div><p class="adm-kpi-val" id="kpi-students">--</p><p class="adm-kpi-label">Students</p></div><span class="adm-kpi-badge" id="kpi-new-today">+0 today</span></div>
      <div class="adm-kpi adm-kpi-emerald"><div class="adm-kpi-icon"><i class="fa-solid fa-building"></i></div><div><p class="adm-kpi-val" id="kpi-companies">--</p><p class="adm-kpi-label">Companies</p></div></div>
      <div class="adm-kpi adm-kpi-amber"><div class="adm-kpi-icon"><i class="fa-solid fa-list-check"></i></div><div><p class="adm-kpi-val" id="kpi-tasks">--</p><p class="adm-kpi-label">Total Tasks</p></div><span class="adm-kpi-badge adm-kpi-badge-green" id="kpi-active-tasks">0 active</span></div>
      <div class="adm-kpi adm-kpi-rose"><div class="adm-kpi-icon"><i class="fa-solid fa-paper-plane"></i></div><div><p class="adm-kpi-val" id="kpi-submissions">--</p><p class="adm-kpi-label">Submissions</p></div><span class="adm-kpi-badge adm-kpi-badge-blue" id="kpi-subs-today">+0 today</span></div>
      <div class="adm-kpi adm-kpi-teal"><div class="adm-kpi-icon"><i class="fa-solid fa-star"></i></div><div><p class="adm-kpi-val" id="kpi-shortlisted">--</p><p class="adm-kpi-label">Shortlisted</p></div></div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="adm-charts-row">
      <div class="adm-card adm-chart-wide">
        <div class="adm-card-head">
          <div><p class="adm-card-eyebrow">Last 30 days</p><h2>Registrations</h2></div>
          <div class="adm-legend"><span class="adm-leg adm-leg-purple"></span>Students <span class="adm-leg adm-leg-emerald"></span>Companies</div>
        </div>
        <div class="adm-chart-wrap"><canvas id="chartReg"></canvas></div>
      </div>
      <div class="adm-card">
        <div class="adm-card-head"><div><p class="adm-card-eyebrow">Breakdown</p><h2>Submission Status</h2></div></div>
        <div class="adm-chart-wrap adm-chart-doughnut"><canvas id="chartStatus"></canvas></div>
        <div class="adm-doughnut-legend" id="statusLegend"></div>
      </div>
    </div>

    <!-- CHARTS ROW 2 -->
    <div class="adm-charts-row">
      <div class="adm-card adm-chart-wide">
        <div class="adm-card-head"><div><p class="adm-card-eyebrow">Last 30 days</p><h2>Daily Submissions</h2></div></div>
        <div class="adm-chart-wrap"><canvas id="chartSubs"></canvas></div>
      </div>
      <div class="adm-card">
        <div class="adm-card-head"><div><p class="adm-card-eyebrow">Breakdown</p><h2>Task Status</h2></div></div>
        <div class="adm-chart-wrap adm-chart-doughnut"><canvas id="chartTaskStatus"></canvas></div>
        <div class="adm-doughnut-legend" id="taskStatusLegend"></div>
      </div>
    </div>

    <!-- TOP LISTS -->
    <div class="adm-charts-row">
      <div class="adm-card">
        <div class="adm-card-head"><div><p class="adm-card-eyebrow">Most active</p><h2>Top Companies</h2></div></div>
        <div id="topCompanies" class="adm-top-list"></div>
      </div>
      <div class="adm-card">
        <div class="adm-card-head"><div><p class="adm-card-eyebrow">Most popular</p><h2>Top Tasks</h2></div></div>
        <div id="topTasks" class="adm-top-list"></div>
      </div>
    </div>

  </section>

  <!-- USERS SECTION -->
  <section id="sec-users" class="adm-section" style="display:none">
    <div class="adm-card">
      <div class="adm-card-head">
        <div><p class="adm-card-eyebrow">Manage</p><h2>All Users</h2></div>
        <div class="adm-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="userSearch" type="text" placeholder="Search name or email…" oninput="filterUsers()"></div>
      </div>
      <div class="adm-filter-tabs" id="userRoleFilter">
        <button class="active" onclick="filterUsers('all',this)">All</button>
        <button onclick="filterUsers('student',this)">Students</button>
        <button onclick="filterUsers('company',this)">Companies</button>
        <button onclick="filterUsers('blocked',this)">Blocked</button>
      </div>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th>Details</th><th>Actions</th></tr></thead>
          <tbody id="usersTable"></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- TASKS SECTION -->
  <section id="sec-tasks" class="adm-section" style="display:none">
    <div class="adm-card">
      <div class="adm-card-head">
        <div><p class="adm-card-eyebrow">All posted work</p><h2>Tasks</h2></div>
        <div class="adm-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="taskSearch" type="text" placeholder="Search tasks…" oninput="filterTasks()"></div>
      </div>
      <div id="tasksList" class="adm-task-list"></div>
    </div>
  </section>

  <!-- SUBMISSIONS SECTION -->
  <section id="sec-submissions" class="adm-section" style="display:none">
    <div class="adm-card">
      <div class="adm-card-head">
        <div><p class="adm-card-eyebrow">Candidate proof</p><h2>All Submissions</h2></div>
        <div class="adm-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="subSearch" type="text" placeholder="Search submissions…" oninput="filterSubmissions()"></div>
      </div>
      <div class="adm-filter-tabs" id="subStatusFilter">
        <button class="active" onclick="filterSubmissions('all',this)">All</button>
        <button onclick="filterSubmissions('pending',this)">Pending</button>
        <button onclick="filterSubmissions('reviewed',this)">Reviewed</button>
        <button onclick="filterSubmissions('shortlisted',this)">Shortlisted</button>
        <button onclick="filterSubmissions('rejected',this)">Rejected</button>
      </div>
      <div id="submissionsList" class="adm-sub-list"></div>
    </div>
  </section>

</main>

<script src="/assets/js/main.js"></script>
<script>
// ── Data stores ──────────────────────────────────────────────
let allUsers = [], allTasks = [], allSubs = [];
let charts = {};

// ── Section switching ────────────────────────────────────────
function showSection(name, el) {
  document.querySelectorAll('.adm-section').forEach(s => s.style.display = 'none');
  document.getElementById('sec-' + name).style.display = 'block';
  document.querySelectorAll('.adm-link').forEach(a => a.classList.remove('active'));
  if (el) el.classList.add('active');
}

// ── Date badge ───────────────────────────────────────────────
document.getElementById('adm-date').textContent = new Date().toLocaleDateString(undefined, {weekday:'short', month:'short', day:'numeric', year:'numeric'});

// ── Chart helpers ────────────────────────────────────────────
const PALETTE = {
  purple:  '#8b5cf6',
  emerald: '#10b981',
  amber:   '#f59e0b',
  rose:    '#f43f5e',
  teal:    '#14b8a6',
  blue:    '#3b82f6',
  slate:   '#94a3b8',
};

function shortDay(d) {
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString(undefined, {month:'short', day:'numeric'});
}

function makeLineChart(id, labels, datasets) {
  const ctx = document.getElementById(id).getContext('2d');
  if (charts[id]) charts[id].destroy();
  charts[id] = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 12, cornerRadius: 10 } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 }, maxTicksLimit: 8 } },
        y: { grid: { color: 'rgba(148,163,184,.12)' }, ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 }, beginAtZero: true }
      }
    }
  });
}

function makeBarChart(id, labels, data, color) {
  const ctx = document.getElementById(id).getContext('2d');
  if (charts[id]) charts[id].destroy();
  charts[id] = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: color + '99', borderColor: color, borderWidth: 2, borderRadius: 8, borderSkipped: false }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 12, cornerRadius: 10 } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 }, maxTicksLimit: 8 } },
        y: { grid: { color: 'rgba(148,163,184,.12)' }, ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 }, beginAtZero: true }
      }
    }
  });
}

function makeDoughnut(id, labels, data, colors, legendId) {
  const ctx = document.getElementById(id).getContext('2d');
  if (charts[id]) charts[id].destroy();
  charts[id] = new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 12, cornerRadius: 10 } }
    }
  });
  if (legendId) {
    const total = data.reduce((a, b) => a + b, 0);
    document.getElementById(legendId).innerHTML = labels.map((l, i) => `
      <div class="adm-dleg-item">
        <span class="adm-dleg-dot" style="background:${colors[i]}"></span>
        <span class="adm-dleg-label">${escapeHtml(l)}</span>
        <span class="adm-dleg-val">${data[i]}</span>
        <span class="adm-dleg-pct">${total ? Math.round(data[i]/total*100) : 0}%</span>
      </div>`).join('');
  }
}

// ── Load charts ──────────────────────────────────────────────
async function loadCharts() {
  const d = await fetch('/api/admin.php?action=charts').then(r => r.json());
  if (!d.success) return;

  // KPI cards
  document.getElementById('kpi-students').textContent    = d.totals.students;
  document.getElementById('kpi-companies').textContent   = d.totals.companies;
  document.getElementById('kpi-tasks').textContent       = d.totals.tasks;
  document.getElementById('kpi-submissions').textContent = d.totals.submissions;
  document.getElementById('kpi-shortlisted').textContent = d.totals.shortlisted;
  document.getElementById('kpi-new-today').textContent   = '+' + d.totals.new_today + ' today';
  document.getElementById('kpi-active-tasks').textContent= d.totals.active_tasks + ' active';
  document.getElementById('kpi-subs-today').textContent  = '+' + d.totals.subs_today + ' today';

  const labels = d.days.map(shortDay);

  // Registrations line chart
  makeLineChart('chartReg', labels, [
    { label: 'Students',  data: d.registrations.students,  borderColor: PALETTE.purple,  backgroundColor: PALETTE.purple  + '18', fill: true, tension: .4, pointRadius: 3, pointHoverRadius: 6 },
    { label: 'Companies', data: d.registrations.companies, borderColor: PALETTE.emerald, backgroundColor: PALETTE.emerald + '18', fill: true, tension: .4, pointRadius: 3, pointHoverRadius: 6 },
  ]);

  // Daily submissions bar chart
  makeBarChart('chartSubs', labels, d.submissions, PALETTE.rose);

  // Submission status doughnut
  const sKeys = Object.keys(d.statusData);
  const sColors = { pending: PALETTE.amber, reviewed: PALETTE.blue, shortlisted: PALETTE.emerald, rejected: PALETTE.rose };
  makeDoughnut('chartStatus', sKeys, sKeys.map(k => d.statusData[k]), sKeys.map(k => sColors[k] || PALETTE.slate), 'statusLegend');

  // Task status doughnut
  const tKeys = Object.keys(d.taskStatusData);
  const tColors = { active: PALETTE.emerald, closed: PALETTE.slate, draft: PALETTE.amber };
  makeDoughnut('chartTaskStatus', tKeys, tKeys.map(k => d.taskStatusData[k]), tKeys.map(k => tColors[k] || PALETTE.slate), 'taskStatusLegend');

  // Top companies bar
  const tc = d.topCompanies;
  document.getElementById('topCompanies').innerHTML = tc.length
    ? tc.map((c, i) => `<div class="adm-top-item">
        <span class="adm-top-rank">${i+1}</span>
        <div class="adm-top-info"><strong>${escapeHtml(c.company_name)}</strong><div class="adm-top-bar-wrap"><div class="adm-top-bar" style="width:${Math.round(c.sub_count/tc[0].sub_count*100)}%;background:${PALETTE.emerald}"></div></div></div>
        <span class="adm-top-count">${c.sub_count}</span>
      </div>`).join('')
    : '<p class="adm-empty-note">No data yet.</p>';

  // Top tasks bar
  const tt = d.topTasks;
  document.getElementById('topTasks').innerHTML = tt.length
    ? tt.map((t, i) => `<div class="adm-top-item">
        <span class="adm-top-rank">${i+1}</span>
        <div class="adm-top-info"><strong>${escapeHtml(t.title)}</strong><div class="adm-top-bar-wrap"><div class="adm-top-bar" style="width:${Math.round(t.sub_count/tt[0].sub_count*100)}%;background:${PALETTE.purple}"></div></div></div>
        <span class="adm-top-count">${t.sub_count}</span>
      </div>`).join('')
    : '<p class="adm-empty-note">No data yet.</p>';
}

// ── Load table data ──────────────────────────────────────────
async function loadTableData() {
  const d = await fetch('/api/admin.php?action=overview').then(r => r.json());
  if (!d.success) return;
  allUsers = d.users;
  allTasks = d.tasks;
  allSubs  = d.submissions;
  renderUsers();
  renderTasks();
  renderSubmissions();
}

// ── Users ────────────────────────────────────────────────────
let userRoleFilter = 'all';
function filterUsers(role, btn) {
  if (role !== undefined) {
    userRoleFilter = role;
    document.querySelectorAll('#userRoleFilter button').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
  }
  renderUsers();
}
function renderUsers() {
  const q = (document.getElementById('userSearch').value || '').toLowerCase();
  const rows = allUsers.filter(u => {
    const matchRole = userRoleFilter === 'all' || (userRoleFilter === 'blocked' ? u.status === 'blocked' : u.role === userRoleFilter);
    const matchQ = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
    return matchRole && matchQ;
  });
  const tbody = document.getElementById('usersTable');
  if (!rows.length) { tbody.innerHTML = '<tr><td colspan="6" class="adm-empty-cell">No users found.</td></tr>'; return; }
  tbody.innerHTML = rows.map(u => `<tr>
    <td><div class="adm-user-cell"><div class="adm-user-avatar" style="background:${u.role==='company'?'#10b981':'#8b5cf6'}">${escapeHtml((u.name||'?').charAt(0).toUpperCase())}</div><div><strong>${escapeHtml(u.name)}</strong><small>${escapeHtml(u.email)}</small></div></div></td>
    <td>${admBadge(u.role, u.role==='company'?'emerald':'purple')}</td>
    <td>${admBadge(u.status, u.status==='active'?'green':u.status==='blocked'?'red':'amber')}</td>
    <td class="adm-date-cell">${escapeHtml(u.created_at ? u.created_at.split(' ')[0] : '')}</td>
    <td class="adm-detail-cell">${escapeHtml(u.company_name || u.skills || '—')}</td>
    <td><div class="adm-action-btns">
      <button class="adm-btn adm-btn-green" onclick="setUserStatus(${u.id},'active')" title="Approve"><i class="fa-solid fa-check"></i></button>
      <button class="adm-btn adm-btn-amber" onclick="setUserStatus(${u.id},'blocked')" title="Block"><i class="fa-solid fa-ban"></i></button>
      <button class="adm-btn adm-btn-red" onclick="deleteUser(${u.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
    </div></td>
  </tr>`).join('');
}

// ── Tasks ────────────────────────────────────────────────────
function filterTasks() { renderTasks(); }
function renderTasks() {
  const q = (document.getElementById('taskSearch').value || '').toLowerCase();
  const rows = allTasks.filter(t => !q || t.title.toLowerCase().includes(q) || (t.company_name||'').toLowerCase().includes(q));
  const wrap = document.getElementById('tasksList');
  if (!rows.length) { wrap.innerHTML = '<p class="adm-empty-note">No tasks found.</p>'; return; }
  wrap.innerHTML = rows.map(t => `<div class="adm-task-item">
    <div class="adm-task-left">
      <div class="adm-task-icon"><i class="fa-solid fa-briefcase"></i></div>
      <div>
        <strong>${escapeHtml(t.title)}</strong>
        <p>${escapeHtml(t.company_name||'Unknown')} &bull; ${escapeHtml(t.required_skills||'Open skills')}</p>
        <small>${escapeHtml(t.created_at ? t.created_at.split(' ')[0] : '')}</small>
      </div>
    </div>
    ${admBadge(t.status, t.status==='active'?'green':t.status==='closed'?'red':'amber')}
  </div>`).join('');
}

// ── Submissions ──────────────────────────────────────────────
let subStatusFilter = 'all';
function filterSubmissions(status, btn) {
  if (status !== undefined) {
    subStatusFilter = status;
    document.querySelectorAll('#subStatusFilter button').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
  }
  renderSubmissions();
}
function renderSubmissions() {
  const q = (document.getElementById('subSearch').value || '').toLowerCase();
  const rows = allSubs.filter(s => {
    const matchStatus = subStatusFilter === 'all' || s.status === subStatusFilter;
    const matchQ = !q || s.student_name.toLowerCase().includes(q) || s.title.toLowerCase().includes(q);
    return matchStatus && matchQ;
  });
  const wrap = document.getElementById('submissionsList');
  if (!rows.length) { wrap.innerHTML = '<p class="adm-empty-note">No submissions found.</p>'; return; }
  wrap.innerHTML = rows.map(s => `<div class="adm-sub-item">
    <div class="adm-sub-left">
      <div class="adm-sub-avatar">${escapeHtml((s.student_name||'S').charAt(0).toUpperCase())}</div>
      <div>
        <strong>${escapeHtml(s.student_name)}</strong>
        <p>${escapeHtml(s.title)}</p>
        <small>${escapeHtml(s.submitted_at ? s.submitted_at.split(' ')[0] : '')}</small>
      </div>
    </div>
    <div class="adm-sub-right">
      <a href="${escapeHtml(s.submission_link)}" target="_blank" rel="noopener" class="adm-link-btn"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open</a>
      ${admBadge(s.status, s.status==='shortlisted'?'green':s.status==='rejected'?'red':s.status==='reviewed'?'blue':'amber')}
      <div class="adm-action-btns">
        ${['reviewed','shortlisted','rejected'].map(st => `<button class="adm-btn adm-btn-slate" onclick="setSubStatus(${s.id},'${st}')">${st}</button>`).join('')}
      </div>
    </div>
  </div>`).join('');
}

// ── Badge helper ─────────────────────────────────────────────
function admBadge(text, color) {
  const map = { green:'#10b981', red:'#f43f5e', amber:'#f59e0b', blue:'#3b82f6', purple:'#8b5cf6', emerald:'#10b981', slate:'#64748b' };
  const c = map[color] || '#64748b';
  return `<span class="adm-badge" style="background:${c}22;color:${c};border-color:${c}44">${escapeHtml(text||'')}</span>`;
}

// ── Actions ──────────────────────────────────────────────────
async function setUserStatus(id, status) {
  const fd = new FormData(); fd.append('action','set_status'); fd.append('id',id); fd.append('status',status);
  const d = await postForm('/api/admin.php', fd);
  showToast(d.message, d.success ? 'success' : 'error');
  if (d.success) loadTableData();
}
async function deleteUser(id) {
  if (!confirm('Delete this user and all their data? This cannot be undone.')) return;
  const fd = new FormData(); fd.append('action','delete_user'); fd.append('id',id);
  const d = await postForm('/api/admin.php', fd);
  showToast(d.message, d.success ? 'success' : 'error');
  if (d.success) { loadTableData(); loadCharts(); }
}
async function setSubStatus(id, status) {
  const fd = new FormData(); fd.append('action','status'); fd.append('id',id); fd.append('status',status);
  const d = await postForm('/api/submissions.php', fd);
  showToast(d.message, d.success ? 'success' : 'error');
  if (d.success) loadTableData();
}

function loadAll() { loadCharts(); loadTableData(); }
loadAll();
</script>
</body>
</html>
