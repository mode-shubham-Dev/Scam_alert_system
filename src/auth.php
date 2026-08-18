<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

hardenSession();
session_start();
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

const MAX_LOGIN_ATTEMPTS  = 5;
const LOCK_DURATION_MIN   = 15;

function registerUser(string $name, string $email, string $password): array {
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    $email = strtolower(trim($email));
    $pdo   = getDB();
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'reporter')")
        ->execute([trim($name), $email, $hash]);
    return ['success' => true, 'message' => 'Registered successfully.'];
}

function loginUser(string $email, string $password): array {
    $email = strtolower(trim($email));
    $pdo   = getDB();
    $stmt  = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Constant-time response to prevent user enumeration
        password_verify($password, '$2y$12$invalidhashpadding000000000000000000000000000000000000000');
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    // Check if account is locked
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        $remaining = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
        return ['ok' => false, 'error' => "Account locked due to too many failed attempts. Try again in {$remaining} minute(s)."];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = (int)$user['login_attempts'] + 1;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?")
                ->execute([$attempts, LOCK_DURATION_MIN, $user['id']]);
            return ['ok' => false, 'error' => 'Too many failed attempts. Account locked for ' . LOCK_DURATION_MIN . ' minutes.'];
        }
        $pdo->prepare("UPDATE users SET login_attempts = ? WHERE id = ?")
            ->execute([$attempts, $user['id']]);
        $left = MAX_LOGIN_ATTEMPTS - $attempts;
        return ['ok' => false, 'error' => "Invalid email or password. {$left} attempt(s) remaining before lockout."];
    }

    // Successful login — reset attempts and start fresh session
    $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?")
        ->execute([$user['id']]);
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role'],
    ];
    return ['ok' => true];
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(string|array $roles): void {
    requireLogin();
    if (!in_array(currentUser()['role'], (array)$roles, true)) {
        http_response_code(403);
        die('Access denied.');
    }
}
