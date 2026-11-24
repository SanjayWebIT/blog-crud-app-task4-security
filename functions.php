<?php
declare(strict_types=1);

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user_role(PDO $pdo): string {
    if (!is_logged_in()) return 'guest';
    // Read the current role from the database so role changes take effect immediately.
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_SESSION['user_id']]);
    $role = $stmt->fetchColumn();
    if (!is_string($role)) {
        $_SESSION = [];
        session_destroy();
        redirect('login.php');
    }
    $_SESSION['role'] = $role;
    return $role;
}

function require_login(): void {
    if (!is_logged_in()) redirect('login.php');
}

function require_role(PDO $pdo, array $allowedRoles): void {
    require_login();
    if (!in_array(current_user_role($pdo), $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied. You do not have permission to view this page.');
    }
}

function can_manage_post(PDO $pdo, int $ownerId): bool {
    if (!is_logged_in()) return false;
    $role = current_user_role($pdo);
    return in_array($role, ['admin', 'editor'], true) || (int) $_SESSION['user_id'] === $ownerId;
}

function validate_post_input(string $title, string $content): array {
    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    elseif (mb_strlen($title) > 200) $errors[] = 'Title must be 200 characters or fewer.';
    if ($content === '') $errors[] = 'Content is required.';
    elseif (mb_strlen($content) > 50000) $errors[] = 'Content must be 50,000 characters or fewer.';
    return $errors;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid request token. Refresh the page and try again.');
    }
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
