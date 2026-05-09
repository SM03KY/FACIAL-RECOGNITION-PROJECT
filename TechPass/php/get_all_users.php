<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php'; // Or connection logic if you don't use config

$host = "localhost";
$dbname = "techpass";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Get all Active users, order by name
    $stmt = $conn->prepare("SELECT id, stud_no, fname, lname FROM users WHERE status = 'Active' ORDER BY fname ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
}
?>