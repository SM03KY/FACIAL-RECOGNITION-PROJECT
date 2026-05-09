<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$dbname = "techpass";
$username = "root"; 
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Database Connection Failed."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stud_no  = $_POST['student_number'];
    $fname    = $_POST['first_name'];
    $lname    = $_POST['last_name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone_number'];
    $year     = $_POST['year_level'];
    $program  = $_POST['program'];
    $password = $_POST['password'];
    $temp_photo = $_POST['temp_photo_path']; // The path from step 2

    // Check for duplicates
    $check = $conn->prepare("SELECT * FROM users WHERE stud_no = ? OR email = ?");
    $check->execute([$stud_no, $email]);

    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Student number or email already exists.']);
        exit;
    }

    // Rename the temporary photo to the student number
    $final_photo_path = "uploads/" . $stud_no . ".jpg";
    
    // We need to adjust paths because this script runs inside the /php folder
    $old_file = "../" . $temp_photo;
    $new_file = "../" . $final_photo_path;

    if (file_exists($old_file)) {
        rename($old_file, $new_file); // Rename the file in XAMPP
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: Face ID photo was lost. Please refresh.']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $insert = $conn->prepare("
        INSERT INTO users (stud_no, fname, lname, email, password, phone, year, program, status, face_id_path)
        VALUES (:stud_no, :fname, :lname, :email, :password, :phone, :year, :program, 'Active', :face_id_path)
    ");

    try {
        $insert->execute([
            ':stud_no'      => $stud_no,
            ':fname'        => $fname,
            ':lname'        => $lname,
            ':email'        => $email,
            ':password'     => $hashed,
            ':phone'        => $phone,
            ':year'         => $year,
            ':program'      => $program,
            ':face_id_path' => $final_photo_path
        ]);
        echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting...']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>