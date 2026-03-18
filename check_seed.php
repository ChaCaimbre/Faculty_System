<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT COUNT(*) as count FROM schedules");
$row = $res->fetch_assoc();
echo "Count: " . $row['count'];
?>
