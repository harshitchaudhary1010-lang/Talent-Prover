<?php
require_once '../config/db.php';
require_once '../config/session.php';
requireExactRole('company');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT u.*, cp.company_name FROM users u LEFT JOIN company_profiles cp ON cp.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - TalentProve</title>
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
        <a class="sidebar-link" href="/dashboard/company_submissions.php"><i class="fa-solid fa-user-check"></i> Submissions</a>
        <a class="sidebar-link active" href="/dashboard/company_messages.php"><i class="fa-solid fa-comment-dots"></i> Messages</a>
        <a class="sidebar-link" href="/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<main class="dashboard-main company-main fade-in">
    <header class="company-hero">
        <div>
            <p>Candidate communication</p>
            <h1>Messages</h1>
            <span>Send and receive messages with your candidates.</span>
        </div>
        <div class="company-hero-actions">
            <button class="btn-soft lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <button class="btn-primary" onclick="openComposeModal()"><i class="fa-solid fa-pen-to-square"></i> New message</button>
        </div>
    </header>

    <section class="company-page-section">
        <div class="msg-layout" id="msgLayout">
            <!-- Conversation list -->
            <div class="msg-sidebar" id="conversationList">
                <div class="msg-sidebar-head">
                    <h2>Conversations</h2>
                </div>
                <div id="convListInner">
                    <div class="company-empty compact"><i class="fa-solid fa-comment-slash"></i><p>No conversations yet.</p></div>
                </div>
            </div>

            <!-- Thread view -->
            <div class="msg-thread" id="msgThread">
                <div class="msg-thread-empty" id="msgThreadEmpty">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Select a conversation or start a new one.</p>
                </div>
                <div class="msg-thread-inner" id="msgThreadInner" style="display:none;">
                    <div class="msg-thread-head" id="msgThreadHead"></div>
                    <div class="msg-bubble-list" id="msgBubbleList"></div>
                    <form class="msg-reply-form" id="msgReplyForm" onsubmit="sendReply(event)">
                        <div class="emoji-picker-wrap">
                            <button class="emoji-toggle" type="button" onclick="toggleEmojiPanel('replyEmojiPanel')" title="Add emoji"><i class="fa-regular fa-face-smile"></i></button>
                            <div class="emoji-panel" id="replyEmojiPanel">
                                <?php foreach (['🙂','👍','🎉','✅','🙏','🔥','💯','😊','🚀','👏'] as $emoji): ?>
                                    <button type="button" onclick="insertEmoji('msgReplyInput', '<?= $emoji ?>')"><?= $emoji ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="activePartnerId" value="">
                        <textarea class="msg-reply-input" id="msgReplyInput" placeholder="Type your message…" rows="2" required></textarea>
                        <button class="btn-primary msg-send-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Compose modal -->
<div id="composeModal" class="modal-backdrop" data-modal-backdrop>
    <div class="modal-panel p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-black">New message</h2>
            <button class="btn-soft min-h-0 px-3 py-2" onclick="closeModal('composeModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="composeForm" class="grid gap-4" onsubmit="sendNewMessage(event)">
            <div class="form-group">
                <label class="form-label">To (candidate)</label>
                <select class="form-input" id="composeStudent" required>
                    <option value="">Loading candidates…</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <div class="emoji-inline-row">
                    <?php foreach (['🙂','👍','🎉','✅','🙏','🔥','💯','😊','🚀','👏'] as $emoji): ?>
                        <button type="button" onclick="insertEmoji('composeBody', '<?= $emoji ?>')"><?= $emoji ?></button>
                    <?php endforeach; ?>
                </div>
                <textarea class="form-textarea" id="composeBody" placeholder="Write your message here…" required></textarea>
            </div>
            <button class="btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Send message</button>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
let activePartnerId = null;

async function loadConversations() {
    const data = await fetch('/api/messages.php?action=conversations').then(r => r.json());
    const wrap = document.getElementById('convListInner');
    if (!data.success || !data.conversations.length) {
        wrap.innerHTML = '<div class="company-empty compact"><i class="fa-solid fa-comment-slash"></i><p>No conversations yet.</p></div>';
        return;
    }
    wrap.innerHTML = data.conversations.map(c => `
        <button class="msg-conv-item ${activePartnerId == c.partner_id ? 'active' : ''}" data-partner-id="${c.partner_id}" data-partner-name="${escapeHtml(c.partner_name)}">
            <div class="msg-conv-avatar">${escapeHtml(c.partner_name.trim().charAt(0).toUpperCase())}</div>
            <div class="msg-conv-info">
                <span class="msg-conv-name">${escapeHtml(c.partner_name)}</span>
                <span class="msg-conv-preview">${escapeHtml(c.latest_body)}</span>
            </div>
            ${c.unread_count > 0 ? `<span class="msg-unread-badge">${c.unread_count}</span>` : ''}
        </button>
    `).join('');
    wrap.querySelectorAll('.msg-conv-item').forEach(button => {
        button.addEventListener('click', () => {
            openThread(button.dataset.partnerId, button.dataset.partnerName);
        });
    });
}

async function openThread(partnerId, partnerName) {
    activePartnerId = partnerId;
    document.getElementById('msgLayout')?.classList.add('thread-open');
    document.getElementById('activePartnerId').value = partnerId;
    document.getElementById('msgThreadEmpty').style.display = 'none';
    document.getElementById('msgThreadInner').style.display = 'flex';
    document.getElementById('msgThreadHead').innerHTML = `
        <button class="msg-thread-back" type="button" onclick="closeMobileThread()"><i class="fa-solid fa-arrow-left"></i></button>
        <div class="msg-conv-avatar">${escapeHtml(partnerName.trim().charAt(0).toUpperCase())}</div>
        <span>${escapeHtml(partnerName)}</span>
    `;

    const data = await fetch(`/api/messages.php?action=thread&partner_id=${partnerId}`).then(r => r.json());
    const list = document.getElementById('msgBubbleList');
    if (!data.success || !data.messages.length) {
        list.innerHTML = '<p class="msg-no-msgs">No messages yet. Say hello!</p>';
    } else {
        const me = <?= (int)$_SESSION['user_id'] ?>;
        list.innerHTML = data.messages.map(m => `
            <div class="msg-bubble ${m.sender_id == me ? 'msg-bubble-me' : 'msg-bubble-them'}">
                <p>${escapeHtml(m.body)}</p>
                <span>${escapeHtml(m.created_at)}</span>
            </div>
        `).join('');
        list.scrollTop = list.scrollHeight;
    }
    loadConversations();
    updateMessageBadges();
}

function closeMobileThread() {
    document.getElementById('msgLayout')?.classList.remove('thread-open');
}

async function sendReply(e) {
    e.preventDefault();
    const partnerId = document.getElementById('activePartnerId').value;
    const body = document.getElementById('msgReplyInput').value.trim();
    if (!partnerId || !body) return;

    const fd = new FormData();
    fd.append('action', 'send');
    fd.append('receiver_id', partnerId);
    fd.append('body', body);
    const data = await postForm('/api/messages.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById('msgReplyInput').value = '';
        openThread(partnerId, document.getElementById('msgThreadHead').querySelector('span').textContent);
    }
}

async function openComposeModal() {
    const data = await fetch('/api/messages.php?action=candidates').then(r => r.json());
    const sel = document.getElementById('composeStudent');
    if (!data.success || !data.candidates.length) {
        sel.innerHTML = '<option value="">No active students available.</option>';
    } else {
        sel.innerHTML = '<option value="">Select a student...</option>' +
            data.candidates.map(c => {
                const suffix = Number(c.submission_count) > 0 ? ' - submitted work' : '';
                return `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.email)})${suffix}</option>`;
            }).join('');
    }
    openModal('composeModal');
}

async function sendNewMessage(e) {
    e.preventDefault();
    const studentId = document.getElementById('composeStudent').value;
    const body = document.getElementById('composeBody').value.trim();
    if (!studentId || !body) return;

    const fd = new FormData();
    fd.append('action', 'send');
    fd.append('receiver_id', studentId);
    fd.append('body', body);
    const data = await postForm('/api/messages.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById('composeBody').value = '';
        closeModal('composeModal');
        await loadConversations();
        const sel = document.getElementById('composeStudent');
        const name = sel.options[sel.selectedIndex].text.split(' (')[0];
        openThread(studentId, name);
    }
}

async function openInitialThreadFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const studentId = params.get('student_id');
    await loadConversations();
    if (!studentId) return;
    openThread(studentId, params.get('student_name') || 'Student');
}

openInitialThreadFromUrl();
</script>
</body>
</html>
