<?php
// components/debug_chat.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Chat Debugger</h3>";

// 1. Connection
$configPath = '../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
    echo "<p style='color:green'>Found config.php</p>";
} else {
    echo "<p style='color:red'>Config not found</p>";
}

// Manual XAMPP Connection logic just to be sure
$host = '127.0.0.1';
$db   = 'tictracker_v6';
$user = 'root';
$pass = '';
$port = '3307';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Database Connected Successfully</p>";
} catch (PDOException $e) {
    die("<p style='color:red'>DB Connection Failed: " . $e->getMessage() . "</p>");
}

// 2. Test Fetch
$link_id = 8; // We are testing with Link ID 8 (from your URL)
echo "<h4>Testing Fetch for Link ID: $link_id</h4>";

try {
    $stmt = $pdo->prepare("SELECT * FROM chat_log WHERE Link_ID = ?");
    $stmt->execute([$link_id]);
    $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($msgs) . " messages.<br>";
    echo "<pre>" . print_r($msgs, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Fetch Failed: " . $e->getMessage() . "</p>";
}

// 3. Test Insert (The important part)
echo "<h4>Testing Insert Message...</h4>";
try {
    // Trying to insert with NULL for Sender/Receiver
    $sql = "INSERT INTO chat_log (Link_ID, Sender_Type, Chat_Text, Chat_Time, Sender, Receiver) 
            VALUES (?, 'Patient', 'Test Message Debug', NOW(), NULL, NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$link_id]);
    echo "<p style='color:green'><strong>SUCCESS! Message Inserted.</strong></p>";
    echo "Go back to your chat screen, you should see 'Test Message Debug'.";
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold; font-size:1.2em'>INSERT FAILED: " . $e->getMessage() . "</p>";
    
    // Suggestion based on error
    if (strpos($e->getMessage(), "cannot be null") !== false) {
        echo "<br><strong>SOLUTION:</strong> Your database does not allow NULL for Sender/Receiver. <br>Run this SQL in phpMyAdmin: <br><code>ALTER TABLE `chat_log` MODIFY `Sender` int(11) NULL; ALTER TABLE `chat_log` MODIFY `Receiver` int(11) NULL;</code>";
    }
    if (strpos($e->getMessage(), "foreign key constraint fails") !== false) {
        echo "<br><strong>SOLUTION:</strong> Your database requires a valid User ID in Sender/Receiver columns (0 or NULL is failing). <br>Run this in phpMyAdmin: <br><code>ALTER TABLE chat_log DROP FOREIGN KEY chat_log_ibfk_1; ALTER TABLE chat_log DROP FOREIGN KEY chat_log_ibfk_2;</code>";
    }
}
?>