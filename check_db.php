<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT COUNT(*) as faculty FROM faculty");
echo "Faculty: " . $res->fetch_assoc()['faculty'] . "\n";
$res = $db->query("SELECT COUNT(*) as schedules FROM schedules");
echo "Schedules: " . $res->fetch_assoc()['schedules'] . "\n";
$res = $db->query("SELECT COUNT(*) as rooms FROM rooms");
echo "Rooms: " . $res->fetch_assoc()['rooms'] . "\n";
?>
