<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT code, name FROM subjects WHERE code = 'IT-104'");
if ($row = $res->fetch_assoc())
    echo "Found: " . $row['code'] . " - " . $row['name'] . "\n";

// Update IT-104 to MATH101 if it matches Discrete Math (which is common for IT-104)
// The user image shows IT-104 is Discrete Math, but the teacher table says MATH101.
// I'll ensure both exist or just follow the teacher table image for that specific teacher.
?>
