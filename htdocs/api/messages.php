<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/session.php';

// Ensure any auth redirect doesn't break JSON output
function jsonRedirect($url) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.', 'redirect' => $url]);
    exit;
}

// Override session redirect to return JSON instead of HTML
if (!isset($_SESSION['user_id'])) {
    jsonRedirect('/auth/login.php');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo    = getDB();

function loadMessageReceiver(PDO $pdo, int $receiverId) {
    $stmt = $pdo->prepare("SELECT id, name, role, status FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$receiverId]);
    return $stmt->fetch();
}

function canMessageUser(array $sender, array $receiver): bool {
    if (($receiver['status'] ?? '') !== 'active') {
        return false;
    }
    if ($sender['role'] === 'company') {
        return $receiver['role'] === 'student';
    }
    if ($sender['role'] === 'student') {
        return $receiver['role'] === 'company';
    }
    if ($sender['role'] === 'admin') {
        return in_array($receiver['role'], ['student', 'company'], true);
    }
    return false;
}

function sendChatMessage(PDO $pdo, int $receiverId, string $body) {
    $me = [
        'id' => (int)$_SESSION['user_id'],
        'name' => $_SESSION['name'] ?? 'Someone',
        'role' => $_SESSION['role'] ?? '',
    ];

    if (!$receiverId || $body === '') {
        echo json_encode(['success' => false, 'message' => 'Recipient and message are required.']);
        exit;
    }
    if ($receiverId === $me['id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot message yourself.']);
        exit;
    }

    $receiver = loadMessageReceiver($pdo, $receiverId);
    if (!$receiver || !canMessageUser($me, $receiver)) {
        echo json_encode(['success' => false, 'message' => 'You cannot message this user.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
    $stmt->execute([$me['id'], $receiverId, $body]);

    $notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif->execute([$receiverId, 'You have a new message from ' . $me['name'] . '.']);

    echo json_encode([
        'success' => true,
        'message' => 'Message sent.',
        'partner' => [
            'id' => (int)$receiver['id'],
            'name' => $receiver['name'],
            'role' => $receiver['role'],
        ],
    ]);
    exit;
}

try {

switch ($action) {

    case 'unread_count':
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode(['success' => true, 'count' => (int)$stmt->fetchColumn()]);
        break;

    // Send a message. Accepts receiver_id, plus the old student_id/company_id fields.
    case 'send':
    case 'reply':
        $receiverId = (int)($_POST['receiver_id'] ?? $_POST['student_id'] ?? $_POST['company_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        sendChatMessage($pdo, $receiverId, $body);

    // List conversations — compatible with MySQL 5.7+
    case 'conversations':
        $me = (int)$_SESSION['user_id'];

        // Get latest message per conversation partner using a subquery (no window functions)
        $stmt = $pdo->prepare("
            SELECT
                partner_id,
                u.name AS partner_name,
                u.role AS partner_role,
                COALESCE(sp.profile_image, cp.logo) AS partner_image,
                (
                    SELECT m2.body FROM messages m2
                    WHERE (m2.sender_id = :me_a AND m2.receiver_id = partner_id)
                       OR (m2.sender_id = partner_id AND m2.receiver_id = :me_b)
                    ORDER BY m2.created_at DESC LIMIT 1
                ) AS latest_body,
                (
                    SELECT m3.created_at FROM messages m3
                    WHERE (m3.sender_id = :me_c AND m3.receiver_id = partner_id)
                       OR (m3.sender_id = partner_id AND m3.receiver_id = :me_d)
                    ORDER BY m3.created_at DESC LIMIT 1
                ) AS latest_at,
                (
                    SELECT COUNT(*) FROM messages m4
                    WHERE m4.sender_id = partner_id AND m4.receiver_id = :me_e AND m4.is_read = 0
                ) AS unread_count
            FROM (
                SELECT DISTINCT
                    CASE WHEN sender_id = :me_f THEN receiver_id ELSE sender_id END AS partner_id
                FROM messages
                WHERE sender_id = :me_g OR receiver_id = :me_h
            ) partners
            JOIN users u ON u.id = partner_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            LEFT JOIN company_profiles cp ON cp.user_id = u.id
            ORDER BY latest_at DESC
        ");
        $stmt->execute([
            ':me_a' => $me, ':me_b' => $me, ':me_c' => $me, ':me_d' => $me,
            ':me_e' => $me, ':me_f' => $me, ':me_g' => $me, ':me_h' => $me,
        ]);
        echo json_encode(['success' => true, 'conversations' => $stmt->fetchAll()]);
        break;

    // Load full thread between logged-in user and a partner
    case 'thread':
        $me        = (int)$_SESSION['user_id'];
        $partnerId = (int)($_GET['partner_id'] ?? 0);

        if (!$partnerId) {
            echo json_encode(['success' => false, 'message' => 'Partner required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT m.*, u.name AS sender_name, u.role AS sender_role
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$me, $partnerId, $partnerId, $me]);
        $messages = $stmt->fetchAll();

        // Mark incoming messages as read
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?")
            ->execute([$me, $partnerId]);

        echo json_encode(['success' => true, 'messages' => $messages]);
        break;

    // List students for company compose dropdown.
    case 'candidates':
        if ($_SESSION['role'] !== 'company') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, sp.skills,
                (
                    SELECT COUNT(*) FROM submissions s
                    JOIN tasks t ON t.id = s.task_id
                    WHERE t.company_id = ? AND s.student_id = u.id
                ) AS submission_count
            FROM users u
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.role = 'student' AND u.status = 'active'
            ORDER BY submission_count DESC, u.name ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode(['success' => true, 'candidates' => $stmt->fetchAll()]);
        break;

    // List companies for student compose dropdown.
    case 'companies':
        if ($_SESSION['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, cp.company_name, cp.industry
            FROM users u
            LEFT JOIN company_profiles cp ON cp.user_id = u.id
            WHERE u.role = 'company' AND u.status = 'active'
            ORDER BY COALESCE(cp.company_name, u.name) ASC
        ");
        $stmt->execute();
        echo json_encode(['success' => true, 'companies' => $stmt->fetchAll()]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
