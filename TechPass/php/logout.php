<?php
// 1. Start the session (so we can access it to destroy it)
session_start();

// 2. Unset all session variables
$_SESSION = array();

// 3. Destroy the session cookie (Force the browser to forget)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destroy the session storage on the server
session_destroy();

// 5. Redirect to the Login Page
header("Location: ../login_page.html");
exit;
?>