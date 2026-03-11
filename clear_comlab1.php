<?php
require_once 'config.php';
$db = getDB();
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMLAB1'");
if ($room_res->num_rows > 0) {
    $rid = $room_res->fetch_assoc()['id'];
    $db->query("DELETE FROM schedules WHERE room_id = $rid");
    echo "Cleared all schedules for COMLAB1." . PHP_EOL;
}
else {
    echo "COMLAB1 not found." . PHP_EOL;
}
?>
