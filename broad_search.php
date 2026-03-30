<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name, sub.code as sub_code 
                   FROM schedules s 
                   LEFT JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   LEFT JOIN subjects sub ON s.subject_id = sub.id
                   WHERE sub.code LIKE '%LIS%104%' OR f.name LIKE '%SASI%' OR r.name LIKE '%TBA%'");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']}, Fac: '{$row['fac_name']}', Room: '{$row['room_name']}', SubCode: '{$row['sub_code']}', User: {$row['user_id']}\n";
}
?>
