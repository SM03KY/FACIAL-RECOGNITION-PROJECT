<?php
// verify.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $face_id = $data['face_id'];

    // Create a small text file to act as a "flag"
    // This tells your website: "The person with this ID just scanned their face"
    file_put_contents("last_verified.txt", $face_id);

    echo json_encode(["status" => "success"]);
}
?>