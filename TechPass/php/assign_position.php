<?php
session_start();
header('Content-Type: application/json');

// Security Check: Only Administrators allowed
if (!isset($_SESSION['role']) || $_SESSION['position'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit;
}

$host = "localhost"; $dbname = "techpass"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_POST['user_id'];
    $position = $_POST['position'];

    // 1. Deactivate anyone currently holding this position (to prevent duplicates)
    $deactivate = $conn->prepare("UPDATE officers SET is_active = 0 WHERE position = ?");
    $deactivate->execute([$position]);

    // 2. Insert the new officer
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