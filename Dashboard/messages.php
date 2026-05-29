<?php
require_once '../config/db.php';
require_once '../config/session.php';
require_once 'student_layout.php';
requireExactRole('student');

$pdo = getDB();
$profile = getStudentProfile($pdo);

$avatar = !empty($profile['profile_image'])
    ? '<img src="' . htmlspecialchars($profile['profile_image']) . '" alt="Profile picture">'
    : htmlspecialchars(strtoupper(substr($profile['name'], 0, 1)));
?>
<?php studentPageHead('Messages'); ?>
<body class="student-portal">
<div class="student-layout">
    <?php renderStudentSidebar('messages'); ?>
    <main class="student-main">
        <header class="student-topbar">
            <button class="student-menu-btn lg:hidden" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="student-topbar-profile">
                <div class="student-topbar-avatar"><?= $avatar ?></div>
                <div>
                    <span class="student-eyebrow">Inbox</span>
                    <h1>Messages</h1>
                    <p>Your conversations with companies.</p>
                </div>
            </div>
            <button class="student-submit-btn" onclick="openComposeModal()"><i class="fa-solid fa-pen-to-square"></i> New message</button>
        </header>

        <section class="student-page-section">
            <div class="msg-layout" id="msgLayout">
                <!-- Conversation list -->
                <div class="msg-sidebar" id="conversationList">
                    <div class="msg-sidebar-head">
                        <h2>Conversations</h2>
                    </div>
                    <div id="convListInner">
                        <div class="student-messages-empty" style="padding:2rem 1rem;">
                            <i class="fa-regular fa-comment-dots"></i>
                            <p>No conversations yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Thread view -->
                <div class="msg-thread" id="msgThread">
                    <div class="msg-thread-empty" id="msgThreadEmpty">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>Select a conversation to read messages.</p>
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
                            <textarea class="msg-reply-input" id="msgReplyInput" placeholder="Type your reply…" rows="2" required></textarea>
                            <button class="student-submit-btn msg-send-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="composeModal" class="modal-backdrop" data-modal-backdrop>
    <div class="modal-panel p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-black">New message</h2>
            <button class="btn-soft min-h-0 px-3 py-2" onclick="closeModal('composeModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="composeForm" class="grid gap-4" onsubmit="sendNewMessage(event)">
            <div class="form-group">
                <label class="form-label">To (company)</label>
                <select class="form-input" id="composeCompany" required>
                    <option value="">Loading companies...</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <div class="emoji-compose-wrap">
                    <textarea class="form-textarea" id="composeBody" placeholder="Write your message here..." required></textarea>
                    <div class="emoji-inline-row">
                        <?php foreach (['🙂','👍','🎉','✅','🙏','🔥','💯','😊','🚀','👏'] as $emoji): ?>
                            <button type="button" onclick="insertEmoji('composeBody', '<?= $emoji ?>')"><?= $emoji ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <button class="student-submit-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Send message</button>
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
        wrap.innerHTML = '<div style="padding:2rem 1rem;text-align:center;color:#64748b;"><i class="fa-regular fa-comment-dots" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.75rem;"></i><p>No messages yet. When a company reaches out, it will appear here.</p></div>';
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
        list.innerHTML = '<p class="msg-no-msgs">No messages yet.</p>';
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
    const data = await fetch('/api/messages.php?action=companies').then(r => r.json());
    const sel = document.getElementById('composeCompany');
    if (!data.success || !data.companies.length) {
        sel.innerHTML = '<option value="">No companies available.</option>';
    } else {
        sel.innerHTML = '<option value="">Select a company...</option>' +
            data.companies.map(c => {
                const name = c.company_name || c.name;
                const meta = c.industry ? ` - ${c.industry}` : '';
                return `<option value="${c.id}">${escapeHtml(name)}${escapeHtml(meta)}</option>`;
            }).join('');
    }
    openModal('composeModal');
}

async function sendNewMessage(e) {
    e.preventDefault();
    const companyId = document.getElementById('composeCompany').value;
    const body = document.getElementById('composeBody').value.trim();
    if (!companyId || !body) return;

    const fd = new FormData();
    fd.append('action', 'send');
    fd.append('receiver_id', companyId);
    fd.append('body', body);
    const data = await postForm('/api/messages.php', fd);
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById('composeBody').value = '';
        closeModal('composeModal');
        await loadConversations();
        const sel = document.getElementById('composeCompany');
        const name = data.partner?.name || sel.options[sel.selectedIndex].text.split(' - ')[0];
        openThread(companyId, name);
    }
}

loadConversations();
</script>
</body>
</html>