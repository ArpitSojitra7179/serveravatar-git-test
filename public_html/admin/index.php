
<?php

declare(strict_types=1);

// Load application bootstrap
require_once __DIR__ . '/../../includes/bootstrap.php';

// Load authentication functions
require_once __DIR__ . '/../../includes/auth.php';

// Allow only administrators
require_role(['admin']);

$pageTitle = 'Admin Dashboard';

require __DIR__ . '/../../includes/header.php';
?>

<div class="container">

    <div class="auth-card">

        <h1>Admin Dashboard</h1>

        <p class="subtitle">
            Welcome to the administration area.
        </p>

        <div class="alert success">
            Administrator access verified successfully.
        </div>

        <h3>Admin Information</h3>

        <p>
            <strong>Name:</strong>
            <?= e($_SESSION['user_name'] ?? 'Admin') ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= e($_SESSION['user_email'] ?? '') ?>
        </p>

        <p>
            <strong>Role:</strong>
            <?= e($_SESSION['user_role'] ?? '') ?>
        </p>

        <p>
            You have permission to access the administration area.
        </p>

        <a href="../dashboard.php" class="btn">
            Back to User Dashboard
        </a>

    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
