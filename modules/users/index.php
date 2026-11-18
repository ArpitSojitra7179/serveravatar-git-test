<?php
declare(strict_types=1);

// Only administrators can access this page.
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin']);

// Pagination settings.
$perPage = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if (!$page || $page < 1) {
    $page = 1;
}

// Get total number of users.
$countSql = "SELECT COUNT(*) FROM users";
$totalUsers = (int) $pdo->query($countSql)->fetchColumn();

// Calculate pagination.
$totalPages = (int) ceil($totalUsers / $perPage);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

// Fetch users for the current page.
$sql = "
    SELECT id, name, email, role, status, created_at
    FROM users
    ORDER BY id DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Users';

require __DIR__ . '/../../includes/header.php';
?>

<style>
.users-page {
    padding: 25px 0;
}

.users-page-header {
    margin-bottom: 25px;
}

.users-table-wrapper {
    width: 100%;
    overflow-x: auto;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

.users-table th,
.users-table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.users-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
}

.users-table td {
    color: #334155;
    font-size: 14px;
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-blocked {
    background: #fee2e2;
    color: #991b1b;
}

.role-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #e0e7ff;
    color: #3730a3;
    font-size: 12px;
    font-weight: 600;
}

.users-summary {
    margin: 18px 0;
    color: #64748b;
}

.pagination {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 22px;
    align-items: center;
}

.pagination a,
.pagination span {
    padding: 8px 13px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    text-decoration: none;
}

.pagination a {
    color: #334155;
    background: #fff;
}

.pagination a.current {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.pagination .disabled {
    color: #9ca3af;
    background: #f8fafc;
}

.empty-users {
    text-align: center;
    padding: 35px;
    color: #64748b;
}
</style>

<div class="container users-page">

    <div class="users-page-header">
        <h1>Manage Users</h1>
        <p class="subtitle">
            View and manage all registered accounts.
        </p>

        <a href="index.php" class="btn">
            Back to Admin Dashboard
        </a>
    </div>

    <div class="users-summary">
        Total registered users: <strong><?= e((string) $totalUsers) ?></strong>
    </div>

    <div class="users-table-wrapper">

        <?php if (empty($users)): ?>

            <div class="empty-users">
                <h3>No users found</h3>
                <p>There are currently no registered users.</p>
            </div>

        <?php else: ?>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered At</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e((string) $user['id']) ?></td>

                            <td><?= e($user['name']) ?></td>

                            <td><?= e($user['email']) ?></td>

                            <td>
                                <span class="role-badge">
                                    <?= e(ucfirst($user['role'])) ?>
                                </span>
                            </td>

                            <td>
                                <span class="status-badge status-<?= e($user['status']) ?>">
                                    <?= e(ucfirst($user['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= e(date('d M Y', strtotime($user['created_at']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

    </div>

    <?php if ($totalPages > 1): ?>

        <div class="pagination">

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">Previous</a>
            <?php else: ?>
                <span class="disabled">Previous</span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <?php if ($i === $page): ?>
                    <span class="current">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                <?php endif; ?>

            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
            <?php else: ?>
                <span class="disabled">Next</span>
            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
