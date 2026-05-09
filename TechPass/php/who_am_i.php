<?php
// Run this file in your browser: localhost/techpass/php/who_am_i.php
session_start();
require_once 'config.php'; // Load database config

echo "<body style='font-family:sans-serif; padding:30px; background:#f4f4f4;'>";
echo "<div style='background:white; padding:20px; border-radius:10px; max-width:600px; margin:0 auto; box-shadow:0 4px 10px rgba(0,0,0,0.1);'>";
echo "<h1 style='color:#1e6bb8; margin-top:0;'>🕵️ Session Detective</h1>";

// 1. CHECK SESSION
echo "<h3>1. Checking Browser Session...</h3>";
if (!isset($_SESSION['user_id'])) {
    die("<p style='color:red; font-weight:bold;'>❌ You are NOT logged in.</p><p>Go log in as Administrator, then come back to this page.</p></div></body>");
}

echo "<p>✅ <strong>Logged In User ID:</strong> " . $_SESSION['user_id'] . "</p>";
echo "<p>👤 <strong>Saved Role:</strong> " . ($_SESSION['role'] ?? '<i>(Empty)</i>') . "</p>";
echo "<p>👑 <strong>Saved Position:</strong> " . ($_SESSION['position'] ?? '<i>(Empty)</i>') . "</p>";

// 2. CHECK DATABASE
echo "<hr><h3>2. Checking Database Permission...</h3>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Check Officers Table
    $stmt = $conn->prepare("SELECT * FROM officers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $officer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($officer) {
        echo "<p>✅ <strong>Database Found Record:</strong></p>";
        echo "<ul>";
        echo "<li>Position: <strong>" . $officer['position'] . "</strong></li>";
        echo "<li>Active Status: <strong>" . ($officer['is_active'] == 1 ? "Active" : "Inactive (0)") . "</strong></li>";
        echo "</ul>";
        
        // COMPARISON
        echo "<hr><h3>3. The Verdict</h3>";
        if ($officer['position'] === 'Administrator') {
             if (isset($_SESSION['position']) && $_SESSION['position'] === 'Administrator') {
                 echo "<h2 style='color:green;'>✅ EVERYTHING IS PERFECT.</h2>";
                 echo "<p>You should NOT be seeing an error. If you are, your <b>admin_dashboard.html</b> Javascript is broken.</p>";
             } else {
                 echo "<h2 style='color:orange;'>⚠️ SESSION MISMATCH</h2>";
                 echo "<p>The Database says you are Admin, but your Browser Session says: <b>" . ($_SESSION['position'] ?? 'Nothing') . "</b>.</p>";
                 echo "<p><b>FIX:</b> Logout and Log back in.</p>";
             }
        } else {
             echo "<h2 style='color:red;'>❌ NOT AN ADMIN</h2>";
             echo "<p>The database says you are a <b>" . $officer['position'] . "</b>, not an Administrator.</p>";
        }
        
    } else {
        echo "<h2 style='color:red;'>❌ NOT AN OFFICER</h2>";
        echo "<p>User ID " . $_SESSION['user_id'] . " is not in the officers table at all.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Database Error: " . $e->getMessage() . "</p>";
}

echo "</div></body>";
?>