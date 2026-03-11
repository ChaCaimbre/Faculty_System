<?php
require_once 'config.php';
$db = getDB();
$r = $db->query("SELECT name FROM faculty LIMIT 5");
while($row = $r->fetch_assoc()) echo "[".$row['name']."]\n";
?>
