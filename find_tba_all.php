<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name, r.user_id as room_user_id
                   FROM schedules s 
                   JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   WHERE r.name LIKE '%TBA%'");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']}, Teacher: " . ($row['fac_name'] ?? 'N/A') . ", Room: '{$row['room_name']}', User: {$row['user_id']}, RoomUser: {$row['room_user_id']}\n";
}
?>
