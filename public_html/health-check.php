<?php

$checks = [
    'PHP Application' => true,
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'Database Connection' => false,
    'Users Table' => false,
];

try {
    require_once __DIR__ . '/../includes/bootstrap.php';

    $checks['Database Connection'] = true;

    $statement = $pdo->query("SHOW TABLES LIKE 'users'");
    $checks['Users Table'] = $statement->fetchColumn() !== false;

} catch (Throwable $exception) {
    error_log('Health check failed: ' . $exception->getMessage());
}

$pageTitle = 'Health Check - ServerAvatar Testing Lab';

require_once __DIR__ . '/../includes/header.php';

$allPassed = !in_array(false, $checks, true);
?>

<section class="page-heading">
    <h1 class="page-title">System health check</h1>
    <p class="page-description">
        Check the PHP application, required extensions and database environment.
    </p>
</section>

<section class="panel" aria-label="System health results">
    <div class="health-list">
        <?php foreach ($checks as $name => $passed): ?>
            <div class="health-row">
                <span class="health-name"><?= e($name) ?></span>
                <?php if ($passed): ?>
                    <span class="badge badge-success">Passed</span>
                <?php else: ?>
                    <span class="badge badge-danger">Failed</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="health-actions">
    <?php if ($allPassed): ?>
        <span class="pill pill-success">All system checks passed</span>
    <?php else: ?>
        <span class="pill pill-danger">One or more checks need attention</span>
    <?php endif; ?>
    <div class="actions">
        <a href="index.php" class="btn btn-secondary">Back to home</a>
        <a href="health-check.php" class="btn">Run checks again</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
