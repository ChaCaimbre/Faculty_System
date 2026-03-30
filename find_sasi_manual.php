<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT * FROM faculty WHERE name LIKE '%SASI%'");
while($f = $res->fetch_assoc()) {
    $fid = $f['id'];
    $schedules = $db->query("SELECT * FROM schedules WHERE faculty_id = $fid");
    while($s = $schedules->fetch_assoc()) {
        $room = $db->query("SELECT name FROM rooms WHERE id = {$s['room_id']}")->fetch_assoc()['name'];
        echo "ID: {$s['id']}, Fac: '{$f['name']}', Room: '$room', Day: '{$s['day']}', User: {$s['user_id']}\n";
    }
}
?>
