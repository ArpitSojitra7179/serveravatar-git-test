<?php

declare(strict_types=1);

/**
 * Authenticate a user using their email and password.
 *
 * Returns:
 * - success
 * - blocked
 * - invalid
 */
function authenticate_user(
    PDO $pdo,
    string $email,
    string $password
): string {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role, status
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user['password'])
    ) {
        return 'invalid';
    }

    if ($user['status'] !== 'active') {
        return 'blocked';
    }

    // Update the password hash if PHP's default algorithm changes.
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            "UPDATE users SET password = :password WHERE id = :id"
        );

        $update->execute([
            'password' => $newHash,
            'id' => $user['id'],
        ]);
    }

    // Prevent session fixation.
    session_regenerate_id(true);

    // Rotate the CSRF token after authentication.
    unset($_SESSION['csrf_token']);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    return 'success';
}

/**
 * Destroy the current user's session.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]
        );
    }

    session_destroy();
}

/**
 * Check whether the logged-in user has one of the required roles.
 */
function require_role(array $roles): void
{
    require_login();

    $role = $_SESSION['user_role'] ?? '';

    if (!is_string($role) || !in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Access denied. You do not have permission to access this page.');
    }
}