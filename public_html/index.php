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

<section class="hero">
    <span class="eyebrow">Application testing platform</span>
    <h1>Test, monitor and manage your PHP applications.</h1>
    <p>
        A practical testing environment for PHP applications, databases,
        APIs, file uploads, email services and server configurations.
    </p>

    <div class="actions">
        <a href="register.php" class="btn">Create an account</a>
        <a href="login.php" class="btn btn-secondary">Sign in</a>
        <a href="health-check.php" class="btn btn-secondary">System health</a>
    </div>
</section>

<div class="section-heading">
    <h2>Application status</h2>
    <p>A quick overview of the live application environment.</p>
</div>

<div class="status-grid">
    <section class="status-card">
        <h3>PHP application</h3>
        <span class="pill pill-success">Running</span>
        <p>PHP version: <?= e(PHP_VERSION) ?></p>
    </section>

    <section class="status-card">
        <h3>Database connection</h3>
        <?php if ($databaseConnected): ?>
            <span class="pill pill-success">Connected</span>
            <p>The application successfully connected to its database.</p>
        <?php else: ?>
            <span class="pill pill-danger">Not connected</span>
            <p>Check the database configuration and application error log.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
