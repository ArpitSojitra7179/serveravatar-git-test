<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin']);

// Validate user ID.
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

// Fetch user details.
$sql = '
    SELECT id, name, email, role, status, created_at
    FROM users
    WHERE id = :id
    LIMIT 1
';

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$updated = ($_GET['updated'] ?? '') === '1';
$roleUpdated = ($_GET['role_updated'] ?? '') === '1';
$roleError = $_GET['role_error'] ?? '';

$pageTitle = 'User Details';

require __DIR__ . '/../../includes/header.php';
?>

<style>
.user-details-card {
    max-width: 760px;
    margin: 20px auto;
    padding: 28px;
    background: #fff;
    border: 1px solid #e3eaf3;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(29, 48, 84, .06);
}

.user-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
    margin-top: 25px;
}

.user-detail-item {
    min-width: 0;
    padding: 17px;
    background: #f8fafc;
    border: 1px solid #e3eaf3;
    border-radius: 11px;
    overflow-wrap: anywhere;
}

.user-detail-item span {
    display: block;
    margin-bottom: 6px;
    color: #66758c;
    font-size: 13px;
    font-weight: 700;
}

.user-detail-item strong {
    color: #17243b;
    font-size: 15px;
}

/* Separate role management section */
.role-management-card {
    margin-top: 25px;
    padding: 22px;
    background: #f8fafc;
    border: 1px solid #e3eaf3;
    border-radius: 12px;
}

.role-management-card h2 {
    margin: 0 0 10px;
    font-size: 19px;
}

.role-management-card p {
    margin: 0 0 15px;
    color: #66758c;
}

.role-management-card label {
    display: block;
    margin: 12px 0 7px;
    font-weight: 700;
}

.role-management-card select {
    display: block;
    width: 100%;
    max-width: 320px;
    min-height: 46px;
    padding: 10px 12px;
    border: 1px solid #d7e0ed;
    border-radius: 10px;
    background: #fff;
    color: #17243b;
    font: inherit;
}

.role-management-card button {
    margin-top: 15px;
}

/* Navigation buttons */
.detail-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 25px;
}

.detail-actions .btn {
    width: auto;
    min-width: 120px;
    min-height: 46px;
    margin: 0;
}

@media (max-width: 600px) {
    .user-details-grid {
        grid-template-columns: 1fr;
    }

    .user-details-card {
        padding: 20px;
    }

    .role-management-card {
        padding: 18px;
    }

    .detail-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .detail-actions .btn {
        width: 100%;
    }
}
</style>

<div class="user-details-card">

    <h1 class="page-title">User Details</h1>

    <p class="page-description">
        View account information for this registered user.
    </p>

    <?php if ($updated): ?>
        <div class="alert success">
            User information updated successfully.
        </div>
    <?php endif; ?>

    <?php if ($roleUpdated): ?>
        <div class="alert success">
            User role updated successfully.
        </div>
    <?php endif; ?>

    <?php if ($roleError === 'self'): ?>
        <div class="alert error">
            You cannot change your own administrator role.
        </div>
    <?php endif; ?>

    <?php if ($roleError === 'last_admin'): ?>
        <div class="alert error">
            You cannot demote the last administrator.
        </div>
    <?php endif; ?>

    <div class="user-details-grid">

        <div class="user-detail-item">
            <span>User ID</span>
            <strong><?= e((string) $user['id']) ?></strong>
        </div>

        <div class="user-detail-item">
            <span>Full Name</span>
            <strong><?= e($user['name']) ?></strong>
        </div>

        <div class="user-detail-item">
            <span>Email Address</span>
            <strong><?= e($user['email']) ?></strong>
        </div>

        <div class="user-detail-item">
            <span>Role</span>
            <strong><?= e(ucfirst($user['role'])) ?></strong>
        </div>

        <div class="user-detail-item">
            <span>Account Status</span>
            <strong><?= e(ucfirst($user['status'])) ?></strong>
        </div>

        <div class="user-detail-item">
            <span>Registration Date</span>
            <strong>
                <?= e(date('d M Y, h:i A', strtotime($user['created_at']))) ?>
            </strong>
        </div>

    </div>

    <!-- Role management is now outside the action buttons container. -->
    <div class="role-management-card">

        <h2>Manage User Role</h2>

        <p>
            Current role:
            <strong><?= e(ucfirst($user['role'])) ?></strong>
        </p>

        <form action="/admin/change-role.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <input
                type="hidden"
                name="user_id"
                value="<?= e((string) $user['id']) ?>"
            >

            <label for="role">Select Role</label>

            <select name="role" id="role" required>
                <option
                    value="user"
                    <?= $user['role'] === 'user' ? 'selected' : '' ?>
                >
                    User
                </option>

                <option
                    value="admin"
                    <?= $user['role'] === 'admin' ? 'selected' : '' ?>
                >
                    Admin
                </option>
            </select>

            <button type="submit" class="btn">
                Update Role
            </button>

        </form>

    </div>

    <!-- Navigation buttons have their own separate container. -->
    <div class="detail-actions">

        <a
            href="/admin/edit-user.php?id=<?= e((string) $user['id']) ?>"
            class="btn"
        >
            Edit User
        </a>

        <a href="/admin/users.php" class="btn btn-secondary">
            Back to Users
        </a>

        <a href="/admin/index.php" class="btn btn-secondary">
            Admin Dashboard
        </a>

    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
