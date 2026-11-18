
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only administrators can access this page.
require_role(['admin']);

// Fetch user statistics.
$sql = "
    SELECT
        COUNT(*) AS total_users,
        SUM(status = 'active') AS active_users,
        SUM(status = 'blocked') AS blocked_users,
        SUM(role = 'admin') AS total_admins
    FROM users
";

$stats = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

// Set default values.
$totalUsers = (int) ($stats['total_users'] ?? 0);
$activeUsers = (int) ($stats['active_users'] ?? 0);
$blockedUsers = (int) ($stats['blocked_users'] ?? 0);
$totalAdmins = (int) ($stats['total_admins'] ?? 0);

$pageTitle = 'Admin Dashboard';

require __DIR__ . '/../../includes/header.php';
?>

<style>
.admin-dashboard {
    padding: 25px 0;
}

.admin-dashboard-header {
    margin-bottom: 25px;
}

.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.admin-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.admin-stat-card h3 {
    font-size: 15px;
    color: #64748b;
    margin: 0 0 12px;
}

.admin-stat-card .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.admin-welcome-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 22px;
}

@media (max-width: 900px) {
    .admin-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 500px) {
    .admin-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container admin-dashboard">

    <div class="admin-dashboard-header">
        <h1>Admin Dashboard</h1>
        <p class="subtitle">
            Manage your application and monitor registered users.
        </p>
    </div>

    <div class="admin-stats-grid">

        <div class="admin-stat-card">
            <h3>Total Users</h3>
            <p class="stat-number">
                <?= e((string) $totalUsers) ?>
            </p>
        </div>

        <div class="admin-stat-card">
            <h3>Active Users</h3>
            <p class="stat-number">
                <?= e((string) $activeUsers) ?>
            </p>
        </div>

        <div class="admin-stat-card">
            <h3>Blocked Users</h3>
            <p class="stat-number">
                <?= e((string) $blockedUsers) ?>
            </p>
        </div>

        <div class="admin-stat-card">
            <h3>Administrators</h3>
            <p class="stat-number">
                <?= e((string) $totalAdmins) ?>
            </p>
        </div>

    </div>

    <div class="admin-welcome-card">
        <h2>Welcome, <?= e($_SESSION['user_name'] ?? 'Admin') ?>!</h2>

        <p>
            You have successfully accessed the administration area.
        </p>

        <p>
            <strong>Admin Email:</strong>
            <?= e($_SESSION['user_email'] ?? '') ?>
        </p>

        <p>
            <strong>Role:</strong>
            <?= e($_SESSION['user_role'] ?? '') ?>
        </p>

        <a href="../dashboard.php" class="btn">
            Back to User Dashboard
        </a>

        <a href="users.php" class="btn">
            Manage Users
        </a>
    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
