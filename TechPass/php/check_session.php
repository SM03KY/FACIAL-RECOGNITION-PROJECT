<?php
session_start();
header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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
// Allow access if role is 'officer' OR if position is 'Administrator'
$is_officer = ($role === 'officer' || $position === 'Administrator');

// 4. Return the result
echo json_encode([
    'success' => true,
    'user' => [
        'user_id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? 'User',
        'student_number' => $_SESSION['stud_no'],
        'program' => $_SESSION['program'] ?? '',
        'is_officer' => $is_officer,
        'position' => $position 
    ]
]);
?>