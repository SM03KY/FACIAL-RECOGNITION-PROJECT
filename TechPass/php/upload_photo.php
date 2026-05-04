<?php
// Read the raw image data from the ESP32
$imageData = file_get_contents('php://input');

if ($imageData) {
    // Generate a unique filename based on the current time
    $filename = 'capture_' . time() . '.jpg';
    
    // Save the image to the uploads folder
    file_put_contents('../uploads/' . $filename, $imageData);

    // Write the path into status.txt so the website can display it!
    file_put_contents('status.txt', 'uploads/' . $filename);
    
    echo "Success";
} else {
    echo "No image data received";
}
?>