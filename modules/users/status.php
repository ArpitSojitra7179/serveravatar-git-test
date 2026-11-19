<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin']);

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Validate CSRF token.
verify_csrf();

// Validate user ID.
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

// Validate requested status.
$newStatus = $_POST['status'] ?? '';

if (!in_array($newStatus, ['active', 'blocked'], true)) {
    http_response_code(400);
    exit('Invalid account status.');
}

// Prevent administrators from blocking their own account.
if (
    $newStatus === 'blocked' &&
    $userId === (int) $_SESSION['user_id']
) {
    redirect('/admin/view-user.php?id=' . $userId . '&status_error=self');
}

// Find target user.
$stmt = $pdo->prepare(
    'SELECT id, role, status FROM users WHERE id = :id LIMIT 1'
);

$stmt->execute(['id' => $userId]);

$targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetUser) {
    http_response_code(404);
    exit('User not found.');
}

$currentStatus = $targetUser['status'];

// No update needed.
if ($currentStatus === $newStatus) {
    redirect('/admin/view-user.php?id=' . $userId);
}

// Prevent blocking the last active administrator.
if (
    $targetUser['role'] === 'admin' &&
    $currentStatus === 'active' &&
    $newStatus === 'blocked'
) {
    $adminCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM users
         WHERE role = 'admin' AND status = 'active'"
    )->fetchColumn();

    if ($adminCount <= 1) {
        redirect('/admin/view-user.php?id=' . $userId . '&status_error=last_admin');
    }
}

// Update account status.
$update = $pdo->prepare(
    'UPDATE users SET status = :status WHERE id = :id'
);

$update->execute([
    'status' => $newStatus,
    'id' => $userId,
]);

redirect('/admin/view-user.php?id=' . $userId . '&status_updated=1');
