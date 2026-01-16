<?php
// auth.php – Login & signup API (accepts JSON or normal form POST)
session_start();
header('Content-Type: application/json');

// --- Read input: try JSON first, then fall back to $_POST ---
$data = null;

// Try JSON body
$raw = file_get_contents('php://input');
if (!empty($raw)) {
    $tmp = json_decode($raw, true);
    if (is_array($tmp)) {
        $data = $tmp;
    }
}

// If JSON failed or was empty, try regular POST
if ($data === null || !is_array($data) || empty($data)) {
    if (!empty($_POST)) {
        $data = $_POST;
    }
}

// If still nothing, bail out
if (!is_array($data) || empty($data)) {
    echo json_encode(['ok' => false, 'message' => 'Invalid request.']);
    exit;
}

// Extract fields
$action   = $data['action']  ?? '';
$email    = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';
$name     = trim($data['name'] ?? '');
$role     = $data['role'] ?? 'patient';

// Basic validation
if (!in_array($action, ['login', 'signup'], true)) {
    echo json_encode(['ok' => false, 'message' => 'Unknown action.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['ok' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

require_once __DIR__ . '/config.php';
if (!isset($conn) || !$conn) {
    echo json_encode(['ok' => false, 'message' => 'Database connection error.']);
    exit;
}

// normalise role
$role = ($role === 'professional') ? 'professional' : 'patient';

//
// SIGN UP
//
if ($action === 'signup') {
    // Check if email already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        echo json_encode(['ok' => false, 'message' => 'Database error.']);
        exit;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['ok' => false, 'message' => 'An account with this email already exists.']);
        exit;
    }
    $stmt->close();

    // Create account
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('INSERT INTO users (email, password_hash, name, role, created_at) VALUES (?, ?, ?, ?, NOW())');
    if (!$stmt) {
        echo json_encode(['ok' => false, 'message' => 'Database error.']);
        exit;
    }
    $stmt->bind_param('ssss', $email, $hash, $name, $role);

    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['ok' => false, 'message' => 'Could not create account.']);
        exit;
    }

    $userId = $stmt->insert_id;
    $stmt->close();

    // Log in user
    $_SESSION['uid']   = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['name']  = $name;
    $_SESSION['role']  = $role;

    echo json_encode(['ok' => true]);
    exit;
}

//
// LOGIN
//
if ($action === 'login') {
    $stmt = $conn->prepare('SELECT id, email, password_hash, name, role FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        echo json_encode(['ok' => false, 'message' => 'Database error.']);
        exit;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['ok' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    $_SESSION['uid']   = (int)$user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name']  = $user['name'] ?? '';
    $_SESSION['role']  = $user['role'] ?? 'patient';

    echo json_encode(['ok' => true]);
    exit;
}
