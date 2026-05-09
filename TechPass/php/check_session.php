<?php
session_start();
header('Content-Type: application/json');

// 1. Check if user is logged in (Using the correct variable from login.php!)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated',
        'redirect' => 'login_page.html'
    ]);
    exit;
}

// 2. GET SAVED SESSION DATA
$role = $_SESSION['role'] ?? 'student';
$position = $_SESSION['position'] ?? 'Student';

// 3. VALIDATE OFFICER STATUS
$is_officer = ($role === 'officer' || $position === 'Administrator');

// 4. Return the result
echo json_encode([
    'success' => true,
    'user' => [
        // Use stud_no since login.php doesn't set user_id
        'user_id' => $_SESSION['stud_no'], 
        'full_name' => $_SESSION['full_name'] ?? 'TechPass Student', 
        'student_number' => $_SESSION['stud_no'],
        'program' => $_SESSION['program'] ?? '',
        'is_officer' => $is_officer,
        'position' => $position
    ]
]);
?>