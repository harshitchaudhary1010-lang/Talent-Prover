<?php
/**
 * List all users in database
 */
require_once __DIR__ . '/config/db.php';

$pdo = getDB();
$stmt = $pdo->query("SELECT id, name, email, role, status FROM users ORDER BY id");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #4CAF50;
            color: white;
        }
        .student { color: #2196F3; }
        .company { color: #FF9800; }
        .admin { color: #9C27B0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>All Users in Database</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td class="<?= htmlspecialchars($user['role']) ?>">
                    <?= htmlspecialchars(ucfirst($user['role'])) ?>
                </td>
                <td><?= htmlspecialchars($user['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><strong>Total users:</strong> <?= count($users) ?></p>
    </div>
</body>
</html>
