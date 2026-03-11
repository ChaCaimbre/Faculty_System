<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT s.*, sub.code, f.name as faculty, r.name as room 
                  FROM schedules s 
                  JOIN subjects sub ON s.subject_id = sub.id 
                  JOIN faculty f ON s.faculty_id = f.id 
                  JOIN rooms r ON s.room_id = r.id 
                  WHERE r.name = 'COMLAB4' 
                  ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time");
while ($row = $res->fetch_assoc()) {
    echo "{$row['day']} {$row['start_time']}-{$row['end_time']} | {$row['code']} | {$row['section']} | {$row['faculty']}" . PHP_EOL;
}
?>
