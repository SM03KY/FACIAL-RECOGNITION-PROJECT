<?php
session_start();
echo "<h1>🕵️ Session Inspector</h1>";

// 1. Check if a session exists at all
if (empty($_SESSION)) {
    echo "<h2 style='color:red'>❌ No Session Found</h2>";
    echo "<p>You are not logged in. Go to the login page, log in, and then COME BACK here immediately without closing the browser.</p>";
    exit;
}

// 2. Print all session variables
echo "<h3>Current Session Variables:</h3>";
echo "<pre style='background:#f4f4f4; padding:15px;'>";
print_r($_SESSION);
echo "</pre>";

// 3. Check Officer Status logic
$role_check = isset($_SESSION['role']) ? $_SESSION['role'] : 'NULL';
$is_officer_check = (isset($_SESSION['role']) && $_SESSION['role'] === 'officer') ? 'TRUE' : 'FALSE';

echo "<h3>Diagnosis:</h3>";
echo "<ul>";
echo "<li><strong>Role Variable:</strong> $role_check</li>";
echo "<li><strong>Is Officer?</strong> $is_officer_check</li>";
echo "</ul>";

if ($is_officer_check === 'FALSE') {
    echo "<h2 style='color:red'>❌ FAILURE: The System thinks you are a Student.</h2>";
    echo "<p>This means <code>login.php</code> did not find you in the <code>officers</code> table.</p>";
} else {
    echo "<h2 style='color:green'>✅ SUCCESS: The System knows you are an Officer!</h2>";
    echo "<p>If you still see 'Access Denied', the problem is inside <code>check_session.php</code>.</p>";
}
?>