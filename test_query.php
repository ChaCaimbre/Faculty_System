<?php
require_once 'config.php';
$db = getDB();
$query = "SELECT s.*, r.name as room_name, f.name as faculty_name, f.designated_campus, sub.code as subject_code, sub.name as subject_name 
          FROM schedules s
          JOIN rooms r ON s.room_id = r.id
          JOIN faculty f ON s.faculty_id = f.id
          JOIN subjects sub ON s.subject_id = sub.id";
$result = $db->query($query);
if (!$result) {
    echo "Query Error: " . $db->error;
} else {
    echo "Query OK. Rows: " . $result->num_rows;
}
?>
