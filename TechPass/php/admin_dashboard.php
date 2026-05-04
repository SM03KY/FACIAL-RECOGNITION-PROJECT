<?php
session_start();

// If they don't have a badge, kick them back to the login page!
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_page.html");
    exit;
}

// 1. Connect to your XAMPP Database
$host = "localhost";
$username = "root";
$password = ""; 
$database = "techpass";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Fetch all users from the database
$sql = "SELECT id, stud_no, fname, lname, email, year, program, status, created_at FROM users ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<!-- The rest of your HTML starts down here... -->