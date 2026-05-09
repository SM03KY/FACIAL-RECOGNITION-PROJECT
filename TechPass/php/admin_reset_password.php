<?php
session_start();
header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['position'] !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit;
}

$host = "localhost"; $dbname = "techpass"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $user_id = $_POST['user_id'];
    $new_pass = $_POST['new_password'];

    if (strlen($new_pass) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }

    // Hash the new password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

    // Update DB
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>