<?php

try {
    require_once __DIR__ . '/../includes/bootstrap.php';
} catch (Throwable $exception) {
    error_log('Application bootstrap failed: ' . $exception->getMessage());
    $bootstrapError = true;
}

$databaseConnected = isset($pdo) && $pdo instanceof PDO;

if (!isset($bootstrapError)) {
    $bootstrapError = false;
}

if (function_exists('is_logged_in') && is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Home - ServerAvatar Testing Lab';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="p-5 rounded-4 mb-4 bg-light">
    <span class="badge text-bg-primary mb-3">
        Application Testing Platform
    </span>

    <h1 class="display-5 fw-bold">
        ServerAvatar Testing Lab
    </h1>

    <p class="lead">
        Test PHP applications, databases, APIs, file uploads,
        email services and server configurations.
    </p>

    <a href="register.php" class="btn btn-primary">
        Register
    </a>

    <a href="login.php" class="btn btn-outline-dark">
        Login
    </a>

    <a href="health-check.php" class="btn btn-outline-success">
        Health Check
    </a>
</section>

<h2 class="h4 mb-3">Application Status</h2>

<div class="row g-3">

    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h5>PHP Application</h5>

            <span class="badge text-bg-success">
                Running
            </span>

            <p class="text-muted mt-2 mb-0">
                PHP version: <?= e(PHP_VERSION) ?>
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h5>Database Connection</h5>

            <?php if ($databaseConnected): ?>

                <span class="badge text-bg-success">
                    Connected
                </span>

            <?php else: ?>

                <span class="badge text-bg-danger">
                    Not connected
                </span>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>