<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pageTitle = 'Dashboard';

try {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, role, status, created_at
         FROM users
         WHERE id = :id
         LIMIT 1"
    );

    $stmt->execute([
        'id' => $_SESSION['user_id'],
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        logout_user();
        redirect('login.php');
    }

    // A user blocked after logging in must lose access.
    if ($user['status'] !== 'active') {
        logout_user();
        redirect('login.php');
    }

    // Keep session details synchronized with the database.
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

} catch (Throwable $e) {
    error_log('Dashboard error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to load the dashboard. Please try again later.');
}

require __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-card">

    <h1>Welcome, <?= e($user['name']) ?>!</h1>

    <p class="subtitle">
        You have successfully logged in.
    </p>

    <div class="user-info">

        <div class="info-item">
            <span>User ID</span>
            <strong><?= e((string) $user['id']) ?></strong>
        </div>

        <div class="info-item">
            <span>Name</span>
            <strong><?= e($user['name']) ?></strong>
        </div>

        <div class="info-item">
            <span>Email</span>
            <strong><?= e($user['email']) ?></strong>
        </div>

        <div class="info-item">
            <span>Role</span>
            <strong><?= e(ucfirst($user['role'])) ?></strong>
        </div>

        <div class="info-item">
            <span>Account Status</span>
            <strong><?= e(ucfirst($user['status'])) ?></strong>
        </div>

        <div class="info-item">
            <span>Registered At</span>
            <strong><?= e($user['created_at']) ?></strong>
        </div>

    </div>

    <div class="database-status">
        <span class="status-dot"></span>
        Connected to ServerAvatar Database
    </div>

    <p class="bottom-text">
        Your account information is being retrieved from MySQL.
    </p>



</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>