<?php
require_once 'config.php';
$db = getDB();
$r = $db->query('SELECT DISTINCT day FROM schedules');
while($row = $r->fetch_assoc()) echo "[".$row['day']."]\n";
?>
