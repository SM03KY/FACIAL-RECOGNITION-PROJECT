<?php
session_start();
header('Content-Type: application/json');

// FIX: Check session directly
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database Connection
$host = "localhost";
$dbname = "techpass";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Return empty safe data so the page loads
    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => ['total_payments'=>0, 'verified'=>0, 'pending'=>0, 'open_requests'=>0],
            'pending_payments' => [],
            'requests' => [],
            'announcements' => [],
            'events' => [],
            'activity_log' => []
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>