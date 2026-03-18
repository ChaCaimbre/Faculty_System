<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SHOW COLUMNS FROM faculty");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
echo "--- schedules ---\n";
$res = $db->query("SHOW COLUMNS FROM schedules");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
?>
