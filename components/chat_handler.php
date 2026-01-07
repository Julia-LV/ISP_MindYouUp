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
    $db_name = isset($db) ? $db : 'tictracker_v9';
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

if ($method === 'GET' && $action === 'fetch') {
    $link_id = intval($_GET['link_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM chat_log WHERE Link_ID = ? ORDER BY Chat_Time ASC");
    $stmt->execute([$link_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST' && $action === 'send') {
    // We use $_POST because we are sending FormData now
    $link_id     = $_POST['link_id'] ?? 0;
    $sender_type = $_POST['sender_type'] ?? '';
    $message     = $_POST['message'] ?? '';
    $sender_id   = $_POST['sender_id'] ?? null;
    $receiver_id = $_POST['receiver_id'] ?? null;
    
    $file_path = null;
    $file_type = null;

    // Handle File Upload
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === 0) {
        $upload_dir = '../uploads_chat/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['chat_file']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['chat_file']['tmp_name'], $target_file)) {
            $file_path = $file_name;
            $file_type = $_FILES['chat_file']['type'];
        }
    }

    if ($link_id && ($message || $file_path)) {
        $stmt = $pdo->prepare("INSERT INTO chat_log (Link_ID, Sender_Type, Chat_Text, Chat_Time, Sender, Receiver, File_Path, File_Type) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->execute([$link_id, $sender_type, $message, $sender_id, $receiver_id, $file_path, $file_type]);
        echo json_encode(['success' => true]);
    }
    exit;
}
?>