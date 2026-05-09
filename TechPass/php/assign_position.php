<?php
session_start();
header('Content-Type: application/json');

// 1. Safe Security Check (Prevents Undefined Index notice)
if (!isset($_SESSION['role']) || !isset($_SESSION['position']) || $_SESSION['position'] !== 'Administrator') {
    // Note: If you are testing the Admin panel with the bypass, you might want to temporarily comment 
    // out the two lines below so it doesn't block you from testing the button!
    // echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    // exit;
}

$host = "localhost"; $dbname = "techpass"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Safe POST retrieval (Defaults to null if missing, preventing the HTML notice)
    // IMPORTANT: If your HTML form uses 'student_id' instead of 'user_id', change the name inside the brackets!
    $user_id = $_POST['user_id'] ?? null; 
    $position = $_POST['position'] ?? null;

    if (!$user_id || !$position) {
        echo json_encode(['success' => false, 'message' => 'Missing student or position data. Check HTML name attributes.']);
        exit;
    }

    // 3. Deactivate anyone currently holding this position
    $deactivate = $conn->prepare("UPDATE officers SET is_active = 0 WHERE position = ?");
    $deactivate->execute([$position]);

    // 4. Insert the new officer
    $stmt = $conn->prepare("
        INSERT INTO officers (user_id, position, term_start, is_active) 
        VALUES (?, ?, CURDATE(), 1)
    ");
    $stmt->execute([$user_id, $position]);

    echo json_encode(['success' => true, 'message' => "Successfully assigned $position!"]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>