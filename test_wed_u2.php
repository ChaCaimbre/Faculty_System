<?php
require_once 'config.php';
$uid = 2; // nikki
$term_id = 1;
$db = getDB();
$query = "SELECT s.*, r.name as room_name FROM schedules s JOIN rooms r ON s.room_id=r.id WHERE s.user_id = $uid AND s.term_id = $term_id AND s.day='Wednesday'";
$result = $db->query($query);
echo "Count for user 2 Wed: " . $result->num_rows . "\n";
while($row = $result->fetch_assoc()){
    echo "{$row['room_name']} {$row['day']} {$row['start_time']}\n";
}
?>
