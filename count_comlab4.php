<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT COUNT(*) as total FROM schedules s JOIN rooms r ON s.room_id = r.id WHERE r.name = 'COMLAB4'");
$row = $res->fetch_assoc();
echo "Total COMLAB4 Scheds: " . $row['total'];
?>
