<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pdo = getDB();

try {

switch ($action) {

    case 'overview':
        $users = $pdo->query("SELECT u.id, u.name, u.email, u.role, u.status, u.created_at,
            cp.company_name, cp.industry, sp.skills
            FROM users u
            LEFT JOIN company_profiles cp ON cp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.role != 'admin'
            ORDER BY u.created_at DESC")->fetchAll();

        $tasks = $pdo->query("SELECT t.*, cp.company_name FROM tasks t
            LEFT JOIN company_profiles cp ON cp.user_id = t.company_id
            ORDER BY t.created_at DESC")->fetchAll();

        $submissions = $pdo->query("SELECT s.*, t.title, u.name AS student_name, u.email AS student_email
            FROM submissions s
            JOIN tasks t ON t.id = s.task_id
            JOIN users u ON u.id = s.student_id
            ORDER BY s.submitted_at DESC")->fetchAll();

        echo json_encode(['success' => true, 'users' => $users, 'tasks' => $tasks, 'submissions' => $submissions]);
        break;

    case 'charts':
        // Registrations per day — last 30 days
        $regStmt = $pdo->query("
            SELECT DATE(created_at) AS day, role, COUNT(*) AS cnt
            FROM users
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
              AND role != 'admin'
            GROUP BY day, role
            ORDER BY day ASC
        ");
        $regRows = $regStmt->fetchAll();

        // Build last 30 days array
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $days[] = date('Y-m-d', strtotime("-$i days"));
        }
        $regByDay = ['students' => array_fill_keys($days, 0), 'companies' => array_fill_keys($days, 0)];
        foreach ($regRows as $r) {
            if ($r['role'] === 'student')  $regByDay['students'][$r['day']]  = (int)$r['cnt'];
            if ($r['role'] === 'company')  $regByDay['companies'][$r['day']] = (int)$r['cnt'];
        }

        // Submissions per day — last 30 days
        $subStmt = $pdo->query("
            SELECT DATE(submitted_at) AS day, COUNT(*) AS cnt
            FROM submissions
            WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY day ORDER BY day ASC
        ");
        $subByDay = array_fill_keys($days, 0);
        foreach ($subStmt->fetchAll() as $r) {
            $subByDay[$r['day']] = (int)$r['cnt'];
        }

        // Submission status breakdown
        $statusStmt = $pdo->query("SELECT status, COUNT(*) AS cnt FROM submissions GROUP BY status");
        $statusData = [];
        foreach ($statusStmt->fetchAll() as $r) {
            $statusData[$r['status']] = (int)$r['cnt'];
        }

        // Task status breakdown
        $taskStatusStmt = $pdo->query("SELECT status, COUNT(*) AS cnt FROM tasks GROUP BY status");
        $taskStatusData = [];
        foreach ($taskStatusStmt->fetchAll() as $r) {
            $taskStatusData[$r['status']] = (int)$r['cnt'];
        }

        // Top companies by submissions
        $topCompStmt = $pdo->query("
            SELECT cp.company_name, COUNT(s.id) AS sub_count
            FROM submissions s
            JOIN tasks t ON t.id = s.task_id
            JOIN company_profiles cp ON cp.user_id = t.company_id
            GROUP BY cp.company_name
            ORDER BY sub_count DESC
            LIMIT 5
        ");
        $topCompanies = $topCompStmt->fetchAll();

        // Top tasks by submissions
        $topTaskStmt = $pdo->query("
            SELECT t.title, COUNT(s.id) AS sub_count
            FROM submissions s
            JOIN tasks t ON t.id = s.task_id
            GROUP BY t.id, t.title
            ORDER BY sub_count DESC
            LIMIT 5
        ");
        $topTasks = $topTaskStmt->fetchAll();

        // Platform totals
        $totals = [
            'students'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
            'companies'   => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='company'")->fetchColumn(),
            'tasks'       => (int)$pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
            'submissions' => (int)$pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn(),
            'shortlisted' => (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status='shortlisted'")->fetchColumn(),
            'active_tasks'=> (int)$pdo->query("SELECT COUNT(*) FROM tasks WHERE status='active'")->fetchColumn(),
            'new_today'   => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE() AND role!='admin'")->fetchColumn(),
            'subs_today'  => (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE DATE(submitted_at)=CURDATE()")->fetchColumn(),
        ];

        echo json_encode([
            'success'      => true,
            'days'         => array_values($days),
            'registrations'=> $regByDay,
            'submissions'  => array_values($subByDay),
            'statusData'   => $statusData,
            'taskStatusData'=> $taskStatusData,
            'topCompanies' => $topCompanies,
            'topTasks'     => $topTasks,
            'totals'       => $totals,
        ]);
        break;

    case 'set_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$id || !in_array($status, ['active', 'blocked', 'pending'], true) || $id === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Invalid user or status.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true, 'message' => 'User status updated.']);
        break;

    case 'delete_user':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id || $id === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete this user.']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'User deleted.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
