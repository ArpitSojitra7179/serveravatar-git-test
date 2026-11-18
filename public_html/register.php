<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Register';

$error = '';
$success = '';

$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nameInput = $_POST['name'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        !is_string($nameInput) ||
        !is_string($emailInput) ||
        !is_string($password) ||
        !is_string($confirmPassword)
    ) {
        $error = 'Invalid form input.';
    } else {
        $name = trim($nameInput);
        $email = strtolower(trim($emailInput));

        if ($name === '' || strlen($name) > 100) {
            $error = 'Name is required and must not exceed 100 characters.';
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 150
        ) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password)
                     VALUES (:name, :email, :password)"
                );

                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                ]);

                $success = 'Registration successful! You can now login.';

                $name = '';
                $email = '';

                $_SESSION['registration_success'] =
                    'Registration successful! Please login to your account.';

                redirect('login.php');


            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $error = 'This email is already registered.';
                } else {
                    error_log('Registration error: ' . $e->getMessage());
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="auth-card">

    <h1>Create Account</h1>

    <p class="subtitle">
        Register your account to get started.
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

    <form method="POST" action="register.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label>Full Name</label>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            maxlength="100"
            value="<?= e($name) ?>"
            required
        >

        <label>Email Address</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            maxlength="150"
            value="<?= e($email) ?>"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Minimum 8 characters"
            minlength="8"
            required
        >

        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm your password"
            minlength="8"
            required
        >

        <button type="submit" class="btn">
            Register
        </button>

    </form>

    <p class="bottom-text">
        Already have an account?
        <a href="login.php">Login here</a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>