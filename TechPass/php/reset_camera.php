<?php
// This deletes the old status note so the system is forced to wait for a brand new photo
$file = 'status.txt';
if (file_exists($file)) {
    unlink($file);
}
echo "Ready for new capture";
?>