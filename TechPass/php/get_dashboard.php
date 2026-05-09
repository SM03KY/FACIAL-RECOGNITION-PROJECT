<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 2. Connect to Database
    $db = new Database();
    $conn = $db->connect();
    
    // FIX: Use session variable directly instead of the missing getCurrentUserId() function
    $userId = $_SESSION['user_id'];
    
    // 3. Get Payment Status
    $stmt = $conn->prepare("
        SELECT 
            ft.fee_type_id,
            ft.fee_name,
            ft.amount,
            ft.semester,
            ft.academic_year,
            COALESCE(SUM(p.amount_paid), 0) as paid
        FROM fee_types ft
        LEFT JOIN payments p ON ft.fee_type_id = p.fee_type_id 
            AND p.user_id = :user_id 
            AND p.status IN ('Verified', 'Pending')
        WHERE ft.academic_year = '2024-2025'
        GROUP BY ft.fee_type_id, ft.fee_name, ft.amount, ft.semester, ft.academic_year
    ");
    $stmt->execute(['user_id' => $userId]);
    $payments = $stmt->fetchAll();
    
    // 4. Get Upcoming Events
    $stmt = $conn->prepare("
        SELECT event_name, start_date, start_time, location, is_mandatory
        FROM events
        WHERE start_date >= CURDATE()
        ORDER BY start_date ASC
        LIMIT 5
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
    
    // 5. Get Announcements
    $stmt = $conn->prepare("
        SELECT title, content, post_date
        FROM announcements
        WHERE is_active = 1
        ORDER BY is_pinned DESC, post_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'payments' => $payments,
            'events' => $events,
            'announcements' => $announcements
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>