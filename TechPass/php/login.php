<?php
session_start(); 
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = ""; 
$database = "techpass";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stud_no = $_POST['student_number'] ?? '';
    $raw_password = $_POST['password'] ?? '';
    
    // 👑 THE MASTER QUERY: 
    // Grabs password/name from 'users', AND grabs their position from 'officers' (if they have one)
    $query = "
        SELECT u.password, u.fname, u.lname, u.program, o.position 
        FROM users u 
        LEFT JOIN officers o ON u.stud_no = o.user_id AND o.is_active = 1
        WHERE u.stud_no = ?
    ";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $stud_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind the results (db_position will be NULL if they are just a regular student)
        $stmt->bind_result($hashed_password, $fname, $lname, $program, $db_position);
        $stmt->fetch();
        
        if (password_verify($raw_password, $hashed_password)) {
            
            // Check what the officers table said about them
            if ($db_position === 'Administrator' || $db_position === 'Representative' || $db_position === 'Officer') {
                $target_url = 'admin_dashboard.php'; // Or officer_dashboard.php depending on your setup
                $final_role = 'Administrator';
            } else {
                $target_url = 'student_dashboard.php';
                $final_role = 'Student';
            }

            // Save everything to the session
            $_SESSION['logged_in'] = true;
            $_SESSION['stud_no'] = $stud_no;
            $_SESSION['role'] = $final_role; 
            $_SESSION['position'] = $db_position; // Save their specific title too!
            $_SESSION['full_name'] = $fname . ' ' . $lname;
            $_SESSION['program'] = $program;
            
            echo json_encode([
                'success' => true, 
                'target_url' => $target_url
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Access Denied: Account not found."]);
    }
    
    $stmt->close();
}
$conn->close();
?>