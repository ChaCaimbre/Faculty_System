<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name 
                   FROM schedules s 
                   LEFT JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   LEFT JOIN subjects sub ON s.subject_id = sub.id
                   WHERE f.name LIKE '%SASI%' OR sub.code = 'LIS 104'");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']}, Fac: '{$row['fac_name']}', Room: '{$row['room_name']}', SubID: {$row['subject_id']}, User: {$row['user_id']}\n";
}
?>
