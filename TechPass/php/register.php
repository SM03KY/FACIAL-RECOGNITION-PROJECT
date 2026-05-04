<?php
// 1. Setup JSON headers so your JS receives the correct format
header('Content-Type: application/json');

// 2. Enable Error Reporting (helps you debug if something else breaks)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =====================
// Database Configuration
// =====================
$host = "localhost";
$dbname = "techpass";
$username = "root"; 
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Database Connection Failed: " . $e->getMessage()]);
    exit;
}

// =====================
// Registration Handling
// =====================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. FIXED: Using the correct HTML 'name' attributes
    // We use isset() to avoid errors if a field is missing
    $stud_no = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';
    $fname   = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lname   = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone   = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $year    = isset($_POST['year_level']) ? $_POST['year_level'] : '';
    $program = isset($_POST['program']) ? $_POST['program'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Check if passwords match
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Check for duplicate student number or email
    // 4. FIXED: Table name changed from 'students' to 'users'
    $check = $conn->prepare("SELECT * FROM users WHERE stud_no = ? OR email = ?");
    $check->execute([$stud_no, $email]);

    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Student number or email already exists.']);
        exit;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert new record
    // 5. FIXED: Table name changed from 'students' to 'users'
    $insert = $conn->prepare("
        INSERT INTO users (stud_no, fname, lname, email, password, phone, year, program, status)
        VALUES (:stud_no, :fname, :lname, :email, :password, :phone, :year, :program, 'Active')
    ");

    try {
        $insert->execute([
            ':stud_no' => $stud_no,
            ':fname'   => $fname,
            ':lname'   => $lname,
            ':email'   => $email,
            ':password'=> $hashed,
            ':phone'   => $phone,
            ':year'    => $year,
            ':program' => $program
        ]);

        echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting...']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>