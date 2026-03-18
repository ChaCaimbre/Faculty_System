<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SHOW COLUMNS FROM rooms");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
?>
