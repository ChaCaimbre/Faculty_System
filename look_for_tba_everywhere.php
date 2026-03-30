<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_full_name, r.id as room_full_id 
                   FROM schedules s 
                   JOIN rooms r ON s.room_id = r.id");
while($row = $res->fetch_assoc()) {
    if (strpos(strtoupper($row['room_full_name']), 'TBA') !== false) {
        echo "FOUND TBA: ID {$row['id']}, Room '{$row['room_full_name']}', User {$row['user_id']}\n";
    }
}
?>
