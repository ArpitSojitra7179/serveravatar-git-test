<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$newRole = $_POST['role'] ?? '';

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

if (!in_array($newRole, ['admin', 'user'], true)) {
    http_response_code(400);
    exit('Invalid role selected.');
}

// Prevent administrators from changing their own role.
if ($userId === (int) $_SESSION['user_id']) {
    redirect('/admin/view-user.php?id=' . $userId . '&role_error=self');
}

// Find the target user.
$stmt = $pdo->prepare(
    'SELECT id, role FROM users WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $userId]);

$targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetUser) {
    http_response_code(404);
    exit('User not found.');
}

$currentRole = $targetUser['role'];

// No change is needed.
if ($currentRole === $newRole) {
    redirect('/admin/view-user.php?id=' . $userId);
}

// Prevent demoting the last administrator.
if ($currentRole === 'admin' && $newRole === 'user') {
    $adminCount = (int) $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")
        ->fetchColumn();

    if ($adminCount <= 1) {
        redirect('/admin/view-user.php?id=' . $userId . '&role_error=last_admin');
    }
}

// Update the user's role.
$update = $pdo->prepare(
    'UPDATE users SET role = :role WHERE id = :id'
);

$update->execute([
    'role' => $newRole,
    'id' => $userId,
]);

redirect('/admin/view-user.php?id=' . $userId . '&role_updated=1');
