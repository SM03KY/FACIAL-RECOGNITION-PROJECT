<?php
$statusFile = 'status.txt';

if (file_exists($statusFile)) {
    $status = file_get_contents($statusFile);
    // Send the file path back to the website
    echo trim($status);
    
    // Delete the file so it's ready for the next capture
    unlink($statusFile); 
} else {
    echo "Waiting";
}
?>