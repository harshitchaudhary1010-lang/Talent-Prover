<?php
// Buffer all output so stray errors/warnings never break the JSON
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../config/session.php';

// Top-level safety net — any uncaught error returns valid JSON
function aiMatchError(string $msg): void {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

set_error_handler(function($errno, $errstr) {
    aiMatchError('PHP error: ' . $errstr);
});

try {

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    aiMatchError('Access denied. Please log in as a student.');
}

$pdo = getDB();
if (!$pdo) {
    aiMatchError('Database connection failed.');
}

// ── Fetch student profile ──────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT sp.skills, sp.bio FROM student_profiles sp WHERE sp.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if (!$profile) {
    ob_clean();
    echo json_encode(['success' => true, 'matches' => []]);
    exit;
}

// ── Fetch active tasks ─────────────────────────────────────────────────────
$tasks = $pdo->query(
    "SELECT t.id, t.title, t.required_skills, t.deadline, cp.company_name
     FROM tasks t
     JOIN company_profiles cp ON cp.user_id = t.company_id
     JOIN users u ON u.id = t.company_id
     WHERE t.status = 'active' 
     AND u.status = 'active'
     AND (t.deadline IS NULL OR t.deadline >= CURDATE())
     ORDER BY t.created_at DESC
     LIMIT 50"
)->fetchAll();

if (!$tasks) {
    ob_clean();
    echo json_encode(['success' => true, 'matches' => []]);
    exit;
}

// ── Helpers ────────────────────────────────────────────────────────────────

function ai_normalise(string $s): string {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[.\-_\/]/', '', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
}

// PHP 7-safe substring check
function ai_contains(string $haystack, string $needle): bool {
    return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}

function ai_jaro(string $a, string $b): float {
    if ($a === $b) return 1.0;
    $la = mb_strlen($a);
    $lb = mb_strlen($b);
    if ($la === 0 || $lb === 0) return 0.0;

    $matchDist = max((int)floor(max($la, $lb) / 2) - 1, 0);
    $aM = array_fill(0, $la, false);
    $bM = array_fill(0, $lb, false);
    $m  = 0;

    for ($i = 0; $i < $la; $i++) {
        $start = max(0, $i - $matchDist);
        $end   = min($i + $matchDist + 1, $lb);
        for ($j = $start; $j < $end; $j++) {
            if ($bM[$j] || $a[$i] !== $b[$j]) continue;
            $aM[$i] = $bM[$j] = true;
            $m++;
            break;
        }
    }
    if ($m === 0) return 0.0;

    $t = 0; $k = 0;
    for ($i = 0; $i < $la; $i++) {
        if (!$aM[$i]) continue;
        while (!$bM[$k]) $k++;
        if ($a[$i] !== $b[$k]) $t++;
        $k++;
    }
    return ($m / $la + $m / $lb + ($m - $t / 2) / $m) / 3;
}

function ai_jaroWinkler(string $a, string $b): float {
    $j = ai_jaro($a, $b);
    $p = 0;
    $len = min(4, mb_strlen($a), mb_strlen($b));
    for ($i = 0; $i < $len; $i++) {
        if ($a[$i] === $b[$i]) $p++; else break;
    }
    return $j + $p * 0.1 * (1 - $j);
}

function ai_skillsMatch(string $a, string $b): bool {
    if ($a === $b) return true;
    if (ai_contains($a, $b) || ai_contains($b, $a)) return true;
    // Only run expensive fuzzy check on short strings (< 20 chars)
    if (mb_strlen($a) < 20 && mb_strlen($b) < 20) {
        return ai_jaroWinkler($a, $b) >= 0.92;
    }
    return false;
}

// ── Build student token set ────────────────────────────────────────────────
$rawSkills     = $profile['skills'] ?? '';
$studentTokens = array_values(array_unique(array_filter(
    array_map('ai_normalise', explode(',', $rawSkills))
)));

// Mine single-word keywords from bio (≥3 chars, no spaces)
$bioTokens = [];
foreach (preg_split('/[\s,;\/|]+/', mb_strtolower($profile['bio'] ?? '')) as $w) {
    $w = ai_normalise($w);
    if (mb_strlen($w) >= 3 && mb_strpos($w, ' ') === false) {
        $bioTokens[] = $w;
    }
}
$allTokens = array_values(array_unique(array_merge($studentTokens, $bioTokens)));

// ── Score each task ────────────────────────────────────────────────────────
$matches = [];

foreach ($tasks as $task) {
    $rawRequired = trim($task['required_skills'] ?? '');

    if ($rawRequired === '') {
        $matches[] = [
            'id'             => (int)$task['id'],
            'title'          => (string)$task['title'],
            'company_name'   => (string)$task['company_name'],
            'deadline'       => $task['deadline'],
            'score'          => 50,
            'matched_skills' => [],
            'missing_skills' => ['Open skill set'],
        ];
        continue;
    }

    $rawParts   = array_map('trim', explode(',', $rawRequired));
    $taskTokens = array_map('ai_normalise', $rawParts);
    $total      = count($taskTokens);
    if ($total === 0) continue;

    $matched = [];
    $missing = [];

    foreach ($taskTokens as $idx => $taskSkill) {
        $found = false;
        if ($taskSkill !== '') {
            foreach ($allTokens as $stuSkill) {
                if ($stuSkill !== '' && ai_skillsMatch($taskSkill, $stuSkill)) {
                    $found = true;
                    break;
                }
            }
        }

        $skillLabel = isset($rawParts[$idx]) ? $rawParts[$idx] : $taskSkill;
        if ($found) {
            $matched[] = $skillLabel;
        } else {
            $missing[] = $skillLabel;
        }
    }

    $score = (int)round((count($matched) / $total) * 100);
    if ($score === 0) continue;

    $matches[] = [
        'id'             => (int)$task['id'],
        'title'          => (string)$task['title'],
        'company_name'   => (string)$task['company_name'],
        'deadline'       => $task['deadline'],
        'score'          => $score,
        'matched_skills' => array_values($matched),
        'missing_skills' => array_values($missing),
    ];
}

usort($matches, function($a, $b) { return $b['score'] - $a['score']; });

ob_clean();
echo json_encode(['success' => true, 'matches' => array_values($matches)], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    aiMatchError('Server error: ' . $e->getMessage());
}
