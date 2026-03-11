<?php
require_once 'config.php';
$db = getDB();
$r = $db->query("SELECT * FROM schedules s JOIN rooms r ON s.room_id = r.id WHERE r.name = 'COMPLAB1' AND s.day = 'Tuesday'");
while($row = $r->fetch_assoc()) echo $row['start_time']." - ".$row['end_time']."\n";
?>
