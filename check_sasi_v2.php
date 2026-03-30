<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name 
                   FROM schedules s 
                   LEFT JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   WHERE f.name LIKE '%SASI%'");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']}, Teacher: {$row['fac_name']}, Room: '{$row['room_name']}', Day: {$row['day']}, Time: {$row['start_time']} - {$row['end_time']}\n";
}
?>
