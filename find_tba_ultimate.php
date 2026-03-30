<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.id, f.name as fac, r.name as room, s.user_id 
                   FROM schedules s 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   LEFT JOIN rooms r ON s.room_id = r.id");
while($row = $res->fetch_assoc()) {
    if ($row['room'] === 'TBA') {
        echo "FOUND TBA: ID {$row['id']}, Fac '{$row['fac']}', User {$row['user_id']}\n";
    }
}
?>
