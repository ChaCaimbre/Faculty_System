<?php
require_once 'config.php';
$db = getDB();
$sql = "SELECT s.*, r.name as rn, f.name as fn, sub.code as sc 
        FROM schedules s 
        JOIN rooms r ON s.room_id=r.id 
        JOIN faculty f ON s.faculty_id=f.id 
        JOIN subjects sub ON s.subject_id=sub.id 
        LIMIT 5";
$res = $db->query($sql);
while($row = $res->fetch_assoc()){
    echo "{$row['rn']} {$row['day']} {$row['start_time']} - {$row['end_time']}: {$row['sc']} ({$row['section']}) / {$row['fn']}\n";
}
?>
