<?php
// 1. MUST BE AT THE VERY TOP TO START THE SESSION
session_start(); 

header('Content-Type: application/json');

// Connect to Database
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
    $login_type = $_POST['login_type'] ?? 'student';

    // Decide which table to check based on the dropdown
    $table = ($login_type === 'officer') ? 'officers' : 'users';
    $target_url = ($login_type === 'officer') ? 'admin_dashboard.php' : 'student_dashboard.php';

    // Look up the user in that specific table
    $stmt = $conn->prepare("SELECT password FROM $table WHERE stud_no = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database Error: Table does not exist.']);
        exit;
    }

    $stmt->bind_param("s", $stud_no);
    $stmt->execute();
    $stmt->store_result();

    // Did we find their ID in the table they selected?
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        
        // Verify password
        if (password_verify($raw_password, $hashed_password)) {
            
            // ==========================================
            // 2. CREATE THE USER'S "ID BADGE" (SESSION)
            // ==========================================
            $_SESSION['logged_in'] = true;
            $_SESSION['stud_no'] = $stud_no;
            $_SESSION['role'] = $login_type;
            
            // Password is correct AND they are in the correct table!
            echo json_encode([
                'success' => true, 
                'target_url' => $target_url
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        }
    } else {
        // They tried to log into a role they don't have
        $role_name = ($login_type === 'officer') ? 'Officer' : 'Student';
        echo json_encode(['success' => false, 'message' => "Access Denied: Account not found in $role_name records."]);
    }
    
    $stmt->close();
}
$conn->close();
?>