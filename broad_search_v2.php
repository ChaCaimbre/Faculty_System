<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, sub.code as sub_code, r.name as room_name, f.name as fac_name 
                   FROM schedules s 
                   LEFT JOIN subjects sub ON s.subject_id = sub.id
                   LEFT JOIN rooms r ON s.room_id = r.id
                   LEFT JOIN faculty f ON s.faculty_id = f.id");
while($row = $res->fetch_assoc()) {
    if (strpos($row['sub_code'], 'LIS 104') !== false || $row['fac_name'] === 'SASI' || $row['room_name'] === 'TBA') {
        echo "ID: {$row['id']}, Fac: '{$row['fac_name']}', SubCode: '{$row['sub_code']}', Room: '{$row['room_name']}', Day: '{$row['day']}', User: {$row['user_id']}\n";
    }
}
?>
