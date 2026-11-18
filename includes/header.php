<?php
$pageTitle = $pageTitle ?? 'ServerAvatar Testing Lab';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f7fc">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/style.css?v=3">
</head>
<body>

<nav class="navbar" aria-label="Main navigation">
    <a href="/index.php" class="brand">
        <span class="brand-mark" aria-hidden="true">S</span>
        <span>ServerAvatar Testing Lab</span>
    </a>

    <div class="nav-links">
        <a href="/index.php">Home</a>
        <a href="/health-check.php">Health Check</a>

        <?php if (is_logged_in()): ?>

            <a href="/dashboard.php">Dashboard</a>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="/admin/index.php">Admin Dashboard</a>
                <a href="/admin/users.php">Manage Users</a>
            <?php endif; ?>

            <form action="/logout.php" method="POST" class="nav-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >
                <button type="submit" class="nav-logout">Logout</button>
            </form>

        <?php else: ?>

            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>

        <?php endif; ?>
    </div>
</nav>

<main class="container">
