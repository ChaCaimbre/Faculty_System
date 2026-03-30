<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name 
                   FROM schedules s 
                   JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   WHERE r.name LIKE '%TBA%'");
while($row = $res->fetch_assoc()) echo "ID: {$row['id']}, Fac: '{$row['fac_name']}', Room: '{$row['room_name']}', User: {$row['user_id']}\n";
?>
