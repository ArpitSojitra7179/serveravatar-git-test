<?php

declare(strict_types=1);

/**
 * Escape output before displaying it in HTML.
 */
function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Generate or retrieve the CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify the submitted CSRF token.
 */
function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $token)
    ) {

        http_response_code(419);

        exit(
            'Invalid or expired form token. Please go back and try again.'
        );
    }
}

/**
 * Check whether a user is logged in.
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Require an authenticated user.
 */
function require_login(): void
{
    if (!is_logged_in()) {

        redirect('login.php');
    }
}

/**
 * Redirect to another location.
 */
function redirect(string $location): void
{
    header('Location: ' . $location);

    exit;
}