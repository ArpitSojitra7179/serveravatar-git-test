<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role(['admin']);

// Validate user ID.
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

// Fetch existing user.
$stmt = $pdo->prepare(
    'SELECT id, name, email FROM users WHERE id = :id LIMIT 1'
);

$stmt->execute([':id' => $userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$errors = [];
$name = $user['name'];
$email = $user['email'];
$success = '';

// Process update form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));

    // Validate name.
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > 255) {
        $errors[] = 'Name cannot exceed 255 characters.';
    }

    // Validate email.
    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 255) {
        $errors[] = 'Email cannot exceed 255 characters.';
    }

    // Check duplicate email.
    if ($errors === []) {

        $duplicateStmt = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1'
        );

        $duplicateStmt->execute([
            ':email' => $email,
            ':id' => $userId,
        ]);

        if ($duplicateStmt->fetch()) {
            $errors[] = 'This email address is already registered.';
        }
    }

    // Update user.
    if ($errors === []) {

        $updateStmt = $pdo->prepare(
            'UPDATE users
             SET name = :name, email = :email
             WHERE id = :id'
        );

        $updateStmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $userId,
        ]);

        // Redirect to prevent duplicate form submission.
        redirect('/admin/view-user.php?id=' . $userId . '&updated=1');
    }
}

$pageTitle = 'Edit User';

require __DIR__ . '/../../includes/header.php';
?>

<style>
.edit-user-card {
    max-width: 650px;
    margin: 20px auto;
    padding: 28px;
    background: #fff;
    border: 1px solid #e3eaf3;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(29, 48, 84, .06);
}

.edit-user-card h1 {
    margin-top: 0;
}

.edit-user-card .form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 25px;
}

.edit-user-card .form-actions .btn {
    margin: 0;
}

@media (max-width: 600px) {
    .edit-user-card {
        padding: 20px;
    }
}
</style>

<div class="container">

    <div class="edit-user-card">

        <h1 class="page-title">Edit User</h1>

        <p class="page-description">
            Update the user's name and email address.
        </p>

        <?php if ($errors !== []): ?>

            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="name">Full Name</label>

            <input
                type="text"
                id="name"
                name="name"
                maxlength="255"
                value="<?= e($name) ?>"
                required
            >

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                maxlength="255"
                value="<?= e($email) ?>"
                required
            >

            <div class="form-actions">

                <button type="submit" class="btn">
                    Save Changes
                </button>

                <a
                    href="/admin/view-user.php?id=<?= e((string) $userId) ?>"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
