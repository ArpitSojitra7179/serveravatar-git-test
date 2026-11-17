<?php

require_once __DIR__ . '/../includes/bootstrap.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Login';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !is_string($password) ||
        $password === ''
    ) {
        $error = 'Please enter valid login details.';
    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            redirect('dashboard.php');

        } else {

            $error = 'Incorrect email or password.';
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
