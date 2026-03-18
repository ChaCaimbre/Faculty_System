<?php
$_SESSION['user_id'] = 1;
require_once 'config.php';
$uid = 1;
$term_id = 1;
$db = getDB();
$query = "SELECT s.*, r.name as room_name, f.name as faculty_name, f.designated_campus,
                 sub.code as subject_code, sub.name as subject_name 
          FROM schedules s
          JOIN rooms r    ON s.room_id    = r.id
          JOIN faculty f  ON s.faculty_id = f.id
          JOIN subjects sub ON s.subject_id = sub.id
          WHERE s.user_id = $uid AND s.term_id = $term_id";

$result = $db->query($query);
$schedules = $result->fetch_all(MYSQLI_ASSOC);
echo "Count: " . count($schedules) . "\n";
print_r($schedules[0]);
?>
