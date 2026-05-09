<?php
session_start(); // Start session first!
require_once 'config.php';
header('Content-Type: application/json');

// 1. FIX: Check session variables directly (No functions)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'officer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Officers only.'
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 2. Get Statistics
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM payments) as total_payments,
            (SELECT COUNT(*) FROM payments WHERE status = 'Verified') as verified,
            (SELECT COUNT(*) FROM payments WHERE status = 'Pending') as pending,
            (SELECT COUNT(*) FROM requests WHERE status = 'Pending') as open_requests
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // If stats are empty, use defaults
    if (!$stats) {
        $stats = ['total_payments'=>0, 'verified'=>0, 'pending'=>0, 'open_requests'=>0];
    }
    
    // 3. Get Pending Payments
    $stmt = $conn->prepare("
        SELECT p.*, u.fname, u.lname, u.stud_no as student_number, ft.fee_name,
               CONCAT(u.fname, ' ', u.lname) as student_name
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN fee_types ft ON p.fee_type_id = ft.fee_type_id
        WHERE p.status = 'Pending'
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pendingPayments = $stmt->fetchAll();
    
    // 4. Return Data
    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'pending_payments' => $pendingPayments,
            'requests' => [],      // You can add these queries later
            'announcements' => [], // You can add these queries later
            'events' => [],        // You can add these queries later
            'activity_log' => []
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>