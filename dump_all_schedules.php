<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, r.name as room_name, f.name as fac_name 
                   FROM schedules s 
                   LEFT JOIN rooms r ON s.room_id = r.id 
                   LEFT JOIN faculty f ON s.faculty_id = f.id");
while($row = $res->fetch_assoc()) {
    if ($row['room_name'] === 'TBA' || (isset($_GET['all']) && $_GET['all'] == 1)) {
        echo "ID: {$row['id']}, Fac: '{$row['fac_name']}', Room: '{$row['room_name']}', Day: '{$row['day']}', User: {$row['user_id']}\n";
    }
}
?>
