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

.detail-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 25px;
}

.detail-actions .btn {
    margin: 0;
}

@media (max-width: 600px) {
    .user-details-grid {
        grid-template-columns: 1fr;
    }

    .user-details-card {
        padding: 20px;
    }
}
</style>

<div class="container">

    <div class="user-details-card">

        <h1 class="page-title">User Details</h1>

        <p class="page-description">
            View account information for this registered user.
        </p>

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

        <div class="detail-actions">

            <a href="/admin/users.php" class="btn">
                Back to Users
            </a>

            <a href="/admin/index.php" class="btn btn-secondary">
                Admin Dashboard
            </a>

        </div>

    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
