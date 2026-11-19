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

// Read search and filter values.
$search = trim((string) ($_GET['search'] ?? ''));
$role = (string) ($_GET['role'] ?? '');
$status = (string) ($_GET['status'] ?? '');

// Accept only supported filter values.
if (!in_array($role, ['admin', 'user'], true)) {
    $role = '';
}

if (!in_array($status, ['active', 'blocked'], true)) {
    $status = '';
}

// Build the SQL conditions.
$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(name LIKE :search_name OR email LIKE :search_email)';
    $searchTerm = '%' . $search . '%';
    $params[':search_name'] = $searchTerm;
    $params[':search_email'] = $searchTerm;
}

if ($role !== '') {
    $conditions[] = 'role = :role';
    $params[':role'] = $role;
}

if ($status !== '') {
    $conditions[] = 'status = :status';
    $params[':status'] = $status;
}

$whereSql = '';

if ($conditions !== []) {
    $whereSql = ' WHERE ' . implode(' AND ', $conditions);
}

// Count matching users.
$countSql = 'SELECT COUNT(*) FROM users' . $whereSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalUsers = (int) $countStmt->fetchColumn();

// Calculate pagination.
$totalPages = (int) ceil($totalUsers / $perPage);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

// Fetch matching users.
$sql = '
    SELECT id, name, email, role, status, created_at
    FROM users
    ' . $whereSql . '
    ORDER BY id DESC
    LIMIT :limit OFFSET :offset
';

$stmt = $pdo->prepare($sql);

// Bind search and filter parameters.
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preserve filters in pagination links.
$paginationParams = [];

if ($search !== '') {
    $paginationParams['search'] = $search;
}

if ($role !== '') {
    $paginationParams['role'] = $role;
}

if ($status !== '') {
    $paginationParams['status'] = $status;
}

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

.users-filter-card {
    background: #fff;
    padding: 22px;
    margin-bottom: 20px;
    border: 1px solid #e3eaf3;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(29, 48, 84, .05);
}

.users-filter-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 15px;
    align-items: end;
}

.users-filter-card label {
    margin: 0 0 7px;
}

.users-filter-card input,
.users-filter-card select {
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #d7e0ed;
    border-radius: 9px;
    background: #fff;
    color: #17243b;
    font: inherit;
}

.users-filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 17px;
}

.users-filter-actions .btn {
    margin: 0;
}

.users-table-wrapper {
    width: 100%;
    overflow-x: auto;
    background: #fff;
    border: 1px solid #e3eaf3;
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
    border-bottom: 1px solid #e3eaf3;
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

.status-badge,
.role-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
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
    background: #e0e7ff;
    color: #3730a3;
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
    border: 1px solid #e3eaf3;
    border-radius: 6px;
    text-decoration: none;
}

.pagination a {
    color: #334155;
    background: #fff;
}

.pagination a:hover {
    background: #eef3ff;
}

.pagination .current {
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

@media (max-width: 700px) {
    .users-filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container users-page">

    <div class="users-page-header">
        <h1>Manage Users</h1>

        <p class="subtitle">
            Search, filter and manage all registered accounts.
        </p>

        <a href="/admin/index.php" class="btn">
            Back to Admin Dashboard
        </a>
    </div>

    <!-- Search and filters -->
    <div class="users-filter-card">

        <form method="GET" action="">

            <div class="users-filter-grid">

                <div>
                    <label for="search">Search users</label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        placeholder="Enter name or email..."
                        value="<?= e($search) ?>"
                    >
                </div>

                <div>
                    <label for="role">Filter by role</label>

                    <select id="role" name="role">
                        <option value="">All Roles</option>

                        <option value="admin"
                            <?= $role === 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>

                        <option value="user"
                            <?= $role === 'user' ? 'selected' : '' ?>>
                            User
                        </option>
                    </select>
                </div>

                <div>
                    <label for="status">Filter by status</label>

                    <select id="status" name="status">
                        <option value="">All Statuses</option>

                        <option value="active"
                            <?= $status === 'active' ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="blocked"
                            <?= $status === 'blocked' ? 'selected' : '' ?>>
                            Blocked
                        </option>
                    </select>
                </div>

            </div>

            <div class="users-filter-actions">

                <button type="submit" class="btn">
                    Search & Filter
                </button>

                <a href="users.php" class="btn btn-secondary">
                    Reset Filters
                </a>

            </div>

        </form>

    </div>

    <div class="users-summary">
        Matching users:
        <strong><?= e((string) $totalUsers) ?></strong>
    </div>

    <div class="users-table-wrapper">

        <?php if (empty($users)): ?>

            <div class="empty-users">
                <h3>No users found</h3>
                <p>Try changing your search or filter options.</p>
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

                <?php
                $previousParams = $paginationParams;
                $previousParams['page'] = $page - 1;
                ?>

                <a href="?<?= e(http_build_query($previousParams)) ?>">
                    Previous
                </a>

            <?php else: ?>

                <span class="disabled">Previous</span>

            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <?php
                $pageParams = $paginationParams;
                $pageParams['page'] = $i;
                ?>

                <?php if ($i === $page): ?>

                    <span class="current"><?= $i ?></span>

                <?php else: ?>

                    <a href="?<?= e(http_build_query($pageParams)) ?>">
                        <?= $i ?>
                    </a>

                <?php endif; ?>

            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>

                <?php
                $nextParams = $paginationParams;
                $nextParams['page'] = $page + 1;
                ?>

                <a href="?<?= e(http_build_query($nextParams)) ?>">
                    Next
                </a>

            <?php else: ?>

                <span class="disabled">Next</span>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
