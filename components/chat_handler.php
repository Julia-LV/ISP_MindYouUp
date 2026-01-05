<?php
// FILE: components/chat_handler.php
header('Content-Type: application/json');
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// 1. CONFIG LOAD
$configPath = '../config.php';
if (file_exists($configPath)) { require_once $configPath; }

// 2. DB CONNECTION (XAMPP COMPATIBLE)
if (!isset($pdo)) {
    // Force 127.0.0.1 for Windows XAMPP
    $db_host = isset($host) && $host != 'localhost' ? $host : '127.0.0.1';
    $db_name = isset($db) ? $db : 'tictracker_v6';
    $db_user = isset($user) ? $user : 'root';
    $db_pass = isset($pass) ? $pass : '';
    $db_port = isset($port) ? $port : '3307'; 

    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB Connection Failed: ' . $e->getMessage()]);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// 3. FETCH
if ($method === 'GET' && $action === 'fetch') {
    $link_id = intval($_GET['link_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT * FROM chat_log WHERE Link_ID = ? ORDER BY Chat_Time ASC");
        $stmt->execute([$link_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 4. SEND
if ($method === 'POST' && $action === 'send') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $link_id     = $input['link_id'] ?? 0;
    $sender_type = $input['sender_type'] ?? '';
    $message     = trim($input['message'] ?? '');
    $sender_id   = $input['sender_id'] ?? null;   // New
    $receiver_id = $input['receiver_id'] ?? null; // New
    
    if ($link_id && $message) {
        try {
            // UPDATED: No more NULLs!
            $stmt = $pdo->prepare("
                INSERT INTO chat_log (Link_ID, Sender_Type, Chat_Text, Chat_Time, Sender, Receiver) 
                VALUES (?, ?, ?, NOW(), ?, ?)
            ");
            $stmt->execute([$link_id, $sender_type, $message, $sender_id, $receiver_id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    exit;
}
?>