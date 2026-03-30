<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.* FROM schedules s WHERE room_id = 19");
while($row = $res->fetch_assoc()) echo "ID: {$row['id']}, Faculty: {$row['faculty_id']}, Term: {$row['term_id']}, User: {$row['user_id']}\n";
?>
