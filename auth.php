<?php
// auth.php – Login & signup API (accepts JSON or normal form POST)
session_start();

function json_response(int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_input(): array {
    $raw = file_get_contents('php://input');
    if (is_string($raw) && trim($raw) !== '') {
        $tmp = json_decode($raw, true);
        if (is_array($tmp) && !empty($tmp)) {
            return $tmp;
        }
    }

    if (!empty($_POST) && is_array($_POST)) {
        return $_POST;
    }

    return [];
}

function clean_string($v): string {
    return trim((string)$v);
}

$data = read_input();
if (empty($data)) {
    json_response(400, ['ok' => false, 'message' => 'Invalid request.']);
}

$action   = $data['action'] ?? '';
$email    = strtolower(clean_string($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');
$name     = clean_string($data['name'] ?? '');
$role     = (string)($data['role'] ?? 'patient');

if (!in_array($action, ['login', 'signup'], true)) {
    json_response(400, ['ok' => false, 'message' => 'Unknown action.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(400, ['ok' => false, 'message' => 'Please enter a valid email address.']);
}
if (strlen($password) < 6) {
    json_response(400, ['ok' => false, 'message' => 'Password must be at least 6 characters.']);
}

// Normalise role
$role = ($role === 'professional') ? 'professional' : 'patient';

require_once __DIR__ . '/config.php';
if (!isset($conn) || !$conn) {
    json_response(500, ['ok' => false, 'message' => 'Database connection error.']);
}

//
// SIGNUP
//
if ($action === 'signup') {
    // Check if email already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        json_response(500, ['ok' => false, 'message' => 'Database error.']);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        json_response(409, ['ok' => false, 'message' => 'An account with this email already exists.']);
    }
    $stmt->close();

    // Create account
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('INSERT INTO users (email, password_hash, name, role, created_at) VALUES (?, ?, ?, ?, NOW())');
    if (!$stmt) {
        json_response(500, ['ok' => false, 'message' => 'Database error.']);
    }

    $stmt->bind_param('ssss', $email, $hash, $name, $role);

    if (!$stmt->execute()) {
        $stmt->close();
        json_response(500, ['ok' => false, 'message' => 'Could not create account.']);
    }

    $userId = (int)$stmt->insert_id;
    $stmt->close();

    // Log in user
    $_SESSION['uid']   = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['name']  = $name;
    $_SESSION['role']  = $role;

    json_response(200, ['ok' => true]);
}

//
// LOGIN
//
if ($action === 'login') {
    $stmt = $conn->prepare('SELECT id, email, password_hash, name, role FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        json_response(500, ['ok' => false, 'message' => 'Database error.']);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user || !password_verify($password, (string)$user['password_hash'])) {
        json_response(401, ['ok' => false, 'message' => 'Invalid email or password.']);
    }

    // Optional: upgrade password hash if PHP's default algo/cost changes over time
    if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $upd = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ? LIMIT 1');
        if ($upd) {
            $uid = (int)$user['id'];
            $upd->bind_param('si', $newHash, $uid);
            $upd->execute();
            $upd->close();
        }
    } // password_needs_rehash is intended for this check [web:243]

    $_SESSION['uid']   = (int)$user['id'];
    $_SESSION['email'] = (string)$user['email'];
    $_SESSION['name']  = (string)($user['name'] ?? '');
    $_SESSION['role']  = (string)($user['role'] ?? 'patient');

    json_response(200, ['ok' => true]);
}

// Fallback (should never be hit)
json_response(400, ['ok' => false, 'message' => 'Invalid request.']);
