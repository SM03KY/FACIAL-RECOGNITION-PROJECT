<?php
// This file connects to the camera, downloads the photo, and saves it to XAMPP.
header('Content-Type: application/json');

// Make sure this matches your exact camera IP!
$cam_ip = "192.168.18.54"; 
$upload_dir = "../uploads/"; 

// Create the uploads folder automatically if it doesn't exist yet
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate a random temporary filename so multiple people registering don't overwrite each other
$temp_filename = "temp_" . time() . ".jpg";
$temp_full_path = $upload_dir . $temp_filename;

// Pull the image from the camera (Notice we use port 80 here, not 81)
$image_data = @file_get_contents("http://$cam_ip/capture");

if (!$image_data) {
    echo json_encode(['success' => false, 'message' => 'Camera offline or busy.']);
    exit;
}

// Save the downloaded image into your XAMPP uploads folder
if (file_put_contents($temp_full_path, $image_data)) {
    // Return the path to the HTML so it can display the photo preview on your screen
    echo json_encode(['success' => true, 'path' => "uploads/" . $temp_filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save to XAMPP folder. Check folder permissions.']);
}
?>