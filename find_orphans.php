<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.* FROM schedules s LEFT JOIN rooms r ON s.room_id = r.id WHERE r.id IS NULL");
while($row = $res->fetch_assoc()) echo "ID: {$row['id']}, RoomID: {$row['room_id']}, User: {$row['user_id']}\n";
?>
