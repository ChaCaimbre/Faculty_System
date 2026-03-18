<?php
require_once 'config.php';
$db = getDB();
$sql = "SELECT s.*, r.name as rn 
        FROM schedules s 
        JOIN rooms r ON s.room_id=r.id 
        WHERE s.day='Wednesday' AND s.user_id=1 AND s.term_id=1";
$res = $db->query($sql);
echo "Count: " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()){
    echo "{$row['rn']} {$row['day']} {$row['start_time']} - {$row['end_time']}\n";
}
?>
