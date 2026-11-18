<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Login';

$error = '';
$email = '';

$_SESSION['registration_success'] =
    'Registration successful! Please login to your account.';

redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $emailInput = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (
        !is_string($emailInput) ||
        !is_string($password)
    ) {
        $error = 'Please enter valid login details.';
    } else {
        $email = strtolower(trim($emailInput));

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $password === ''
        ) {
            $error = 'Please enter valid login details.';
        } else {
            try {
                $result = authenticate_user(
                    $pdo,
                    $email,
                    $password
                );

                if ($result === 'success') {
                    redirect('dashboard.php');
                } elseif ($result === 'blocked') {
                    $error = 'Your account is blocked. Please contact the administrator.';
                } else {
                    $error = 'Incorrect email or password.';
                }
            } catch (Throwable $e) {
                error_log('Login error: ' . $e->getMessage());
                $error = 'Login is temporarily unavailable. Please try again.';
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="auth-card">

    <h1>Welcome Back</h1>

    <p class="subtitle">
        Login to your account.
    </p>

    <?php if ($error): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label>Email Address</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            value="<?= e($email) ?>"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit" class="btn">
            Login
        </button>

    </form>

    <p class="bottom-text">
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>