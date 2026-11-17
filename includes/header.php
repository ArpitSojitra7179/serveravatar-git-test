<?php
$pageTitle = $pageTitle ?? 'Git Test Application';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">
        ServerAvatar Git Test
    </a>

    <div class="nav-links">
        <?php if (is_logged_in()): ?>
            <a href="dashboard.php">Dashboard</a>

            <form action="logout.php" method="POST" class="nav-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >
                <button type="submit" class="nav-logout">
                    Logout
                </button>
            </form>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<main class="container">
