<?php
require_once 'config.php';
$db = getDB();

echo "--- rooms indexes ---\n";
$res = $db->query("SHOW INDEX FROM rooms");
while($row = $res->fetch_assoc()) echo $row['Table']." ".$row['Key_name']." ".$row['Column_name']."\n";

echo "--- subjects indexes ---\n";
$res = $db->query("SHOW INDEX FROM subjects");
while($row = $res->fetch_assoc()) echo $row['Table']." ".$row['Key_name']." ".$row['Column_name']."\n";
?>
