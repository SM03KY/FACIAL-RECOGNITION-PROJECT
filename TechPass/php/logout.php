<?php
// 1. Find the current session
session_start();

// 2. Remove all the VIP wristband variables (logged_in, stud_no, etc.)
session_unset();

// 3. Completely destroy the session
session_destroy();

// 4. Redirect them back to the login page
// (Note: We use ../ because this script is inside the /php folder, but the HTML is one folder up)
header("Location: ../login_page.html");
exit();
?>