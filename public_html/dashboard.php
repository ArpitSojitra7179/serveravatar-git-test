<?php

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'Dashboard';

$stmt = $pdo->prepare(
    "SELECT id, name, email, created_at
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    'id' => $_SESSION['user_id']
]);

$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_regenerate_id(true);
    redirect('login.php');
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
