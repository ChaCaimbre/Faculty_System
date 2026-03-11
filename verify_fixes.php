<?php
require_once 'config.php';
$db = getDB();

$checks = [
    ['COMPLAB1', 'Monday', '14:30:00'],
    ['COMPLAB2', 'Monday', '09:00:00'],
    ['COMPLAB5 (CON 103)', 'Monday', '13:00:00']
];

foreach ($checks as $c) {
    $q = "SELECT s.*, f.name as faculty, sub.code as subject, r.name as room 
          FROM schedules s 
          JOIN faculty f ON s.faculty_id = f.id 
          JOIN subjects sub ON s.subject_id = sub.id 
          JOIN rooms r ON s.room_id = r.id 
          WHERE r.name = '{$c[0]}' AND s.day = '{$c[1]}' AND s.start_time = '{$c[2]}'";
    $res = $db->query($q);
    if ($row = $res->fetch_assoc()) {
        echo "{$row['room']} | {$row['day']} | {$row['start_time']} | {$row['subject']} | {$row['section']} | {$row['faculty']}\n";
    }
    else {
        echo "No data for {$c[0]} @ {$c[2]}\n";
    }
}
?>
