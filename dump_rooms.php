<?php
require_once 'config.php';
$db = getDB();
$r = $db->query('SELECT name FROM rooms');
while($row = $r->fetch_assoc()) echo "[".$row['name']."]\n";
?>
