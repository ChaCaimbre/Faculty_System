<?php
$db = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', getenv('DB_NAME') ?: 'faculty_system');
if($db->connect_error) die($db->connect_error);
$db->query("ALTER TABLE faculty ADD COLUMN IF NOT EXISTS designated_campus VARCHAR(100) DEFAULT 'Main Campus'");
echo "Success";
?>
