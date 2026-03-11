<?php
require_once 'config.php';
$db = getDB();

$rooms = [
    'COMPLAB1',
    'COMPLAB2',
    'COMPLAB3',
    'COMPLAB4',
    'COMPLAB5 (CON 103)',
    'COMPLAB6 (CON 104)',
    'COMPLAB7 (CON 105)',
    'CHS (CON 101)',
    'CISCO (CON 102)'
];

$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE rooms");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

$stmt = $db->prepare("INSERT INTO rooms (name) VALUES (?)");
foreach ($rooms as $room) {
    $stmt->bind_param("s", $room);
    $stmt->execute();
}

// Also update schedules if needed, but since we truncated rooms, 
// we should probably re-run the final schedule seed after this.
echo "Rooms updated successfully!";
?>
