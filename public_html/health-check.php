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

    $statement = $pdo->query(
        "SHOW TABLES LIKE 'users'"
    );

    $checks['Users Table'] = $statement->fetchColumn() !== false;

} catch (Throwable $exception) {

    error_log(
        'Health check failed: ' . $exception->getMessage()
    );

}

$pageTitle = 'Health Check - ServerAvatar Testing Lab';

require_once __DIR__ . '/../includes/header.php';

?>

<h1 class="fw-bold mb-2">System Health Check</h1>

<p class="text-muted mb-4">
    Check the current PHP application and database environment.
</p>

<div class="card shadow-sm">
    <div class="card-body">

        <?php foreach ($checks as $name => $passed): ?>

            <div class="d-flex justify-content-between
                        align-items-center border-bottom py-3">

                <span class="fw-semibold">
                    <?= e($name) ?>
                </span>

                <?php if ($passed): ?>

                    <span class="badge text-bg-success">
                        Passed
                    </span>

                <?php else: ?>

                    <span class="badge text-bg-danger">
                        Failed
                    </span>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>
</div>

<div class="mt-4">

    <a href="index.php" class="btn btn-outline-primary">
        Back to Home
    </a>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>