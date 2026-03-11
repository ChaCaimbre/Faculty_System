<?php
require_once 'config.php';
$db = getDB();

// Check if admin exists
$check = $db->query("SELECT id, username FROM users WHERE username = 'admin'");
if ($check->num_rows > 0) {
    $user = $check->fetch_assoc();
    // Reset password
    $newPass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = '$newPass' WHERE username = 'admin'");
    echo "Password reset for user 'admin'. Login with: admin / admin123";
}
else {
    // Create admin
    $newPass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("INSERT INTO users (username, password) VALUES ('admin', '$newPass')");
    echo "Admin user created. Login with: admin / admin123";
}
?>
