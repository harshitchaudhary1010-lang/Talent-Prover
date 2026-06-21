<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/session.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo = getDB();

try {

switch ($action) {
    case 'create':
        if ($_SESSION['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $link = trim($_POST['submission_link'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$taskId || !$link || !filter_var($link, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Choose a task and enter a valid submission URL.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, company_id, title FROM tasks WHERE id = ? AND status = 'active'");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'This task is not available.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE task_id = ? AND student_id = ?");
        $stmt->execute([$taskId, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You already submitted work for this task.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO submissions (task_id, student_id, submission_link, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$taskId, $_SESSION['user_id'], $link, $message]);

        $notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->execute([$task['company_id'], $_SESSION['name'] . ' submitted work for "' . $task['title'] . '".']);

        echo json_encode(['success' => true, 'message' => 'Submission sent successfully.']);
        break;

    case 'list':
        // any logged-in role can list (filtered by role below)
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT s.*, t.title, cp.company_name
                FROM submissions s
                JOIN tasks t ON t.id = s.task_id
                JOIN company_profiles cp ON cp.user_id = t.company_id
                WHERE s.student_id = ?
                ORDER BY s.submitted_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
        } elseif ($_SESSION['role'] === 'company') {
            $stmt = $pdo->prepare("SELECT s.*, t.title, u.name AS student_name, u.email AS student_email,
                    sp.skills, sp.bio, sp.portfolio_link, sp.profile_image
                FROM submissions s
                JOIN tasks t ON t.id = s.task_id
                JOIN users u ON u.id = s.student_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE t.company_id = ?
                ORDER BY s.submitted_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            $stmt = $pdo->query("SELECT s.*, t.title, u.name AS student_name, cp.company_name
                FROM submissions s
                JOIN tasks t ON t.id = s.task_id
                JOIN users u ON u.id = s.student_id
                JOIN company_profiles cp ON cp.user_id = t.company_id
                ORDER BY s.submitted_at DESC");
        }

        echo json_encode(['success' => true, 'submissions' => $stmt->fetchAll()]);
        break;

    case 'status':
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['company', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['pending', 'reviewed', 'shortlisted', 'rejected'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        if ($_SESSION['role'] === 'company') {
            $stmt = $pdo->prepare("SELECT s.student_id, t.company_id, t.title FROM submissions s JOIN tasks t ON t.id = s.task_id WHERE s.id = ? AND t.company_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("SELECT s.student_id, t.company_id, t.title FROM submissions s JOIN tasks t ON t.id = s.task_id WHERE s.id = ?");
            $stmt->execute([$id]);
        }
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Submission not found.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $message = 'Your submission for "' . $row['title'] . '" is now ' . $status . '.';
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->execute([$row['student_id'], $message]);

        echo json_encode(['success' => true, 'message' => 'Submission updated.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
