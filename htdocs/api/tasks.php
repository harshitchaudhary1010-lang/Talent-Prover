<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/session.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo    = getDB();

try {

switch ($action) {
    case 'create':
        if ($_SESSION['role'] !== 'company') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $skills   = trim($_POST['required_skills'] ?? '');
        $deadline = $_POST['deadline'] ?? null;

        if (!$title || !$desc) {
            echo json_encode(['success' => false, 'message' => 'Title and description are required.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO tasks (company_id, title, description, required_skills, deadline) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $desc, $skills, $deadline ?: null]);

        $students = $pdo->query("SELECT id FROM users WHERE role='student' AND status='active'")->fetchAll();
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        foreach ($students as $s) {
            $notifStmt->execute([$s['id'], "New task posted: \"$title\". Check it out!"]);
        }

        echo json_encode(['success' => true, 'message' => 'Task posted successfully!']);
        break;

    case 'list':
        $role = $_SESSION['role'];

        if ($role === 'company') {
            $stmt = $pdo->prepare("SELECT t.*, (SELECT COUNT(*) FROM submissions WHERE task_id=t.id) as sub_count FROM tasks t WHERE t.company_id = ? ORDER BY t.created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
        } elseif ($role === 'admin') {
            $stmt = $pdo->query("SELECT t.*, cp.company_name, (SELECT COUNT(*) FROM submissions WHERE task_id=t.id) as sub_count FROM tasks t LEFT JOIN company_profiles cp ON cp.user_id=t.company_id ORDER BY t.created_at DESC");
        } else {
            // Students: only show active tasks that haven't expired
            $stmt = $pdo->query("SELECT t.*, cp.company_name, (SELECT COUNT(*) FROM submissions WHERE task_id=t.id) as sub_count 
                FROM tasks t 
                JOIN company_profiles cp ON cp.user_id=t.company_id 
                WHERE t.status='active' 
                AND (t.deadline IS NULL OR t.deadline >= CURDATE())
                ORDER BY t.created_at DESC");
        }

        echo json_encode(['success' => true, 'tasks' => $stmt->fetchAll()]);
        break;

    case 'delete':
        if ($_SESSION['role'] !== 'company') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'message' => 'Task deleted.']);
        break;

    case 'toggle_status':
        if ($_SESSION['role'] !== 'company') {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE tasks SET status = IF(status='active','closed','active') WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'message' => 'Task status updated.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
