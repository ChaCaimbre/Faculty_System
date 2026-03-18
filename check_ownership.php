<?php
require_once 'config.php';
$db = getDB();

echo "--- Rooms Count ---\n";
$res = $db->query("SELECT user_id, COUNT(*) as c FROM rooms GROUP BY user_id");
while($row = $res->fetch_assoc()) echo "User " . ($row['user_id'] ?: 'NULL') . ": " . $row['c'] . "\n";

echo "--- Subjects Count ---\n";
$res = $db->query("SELECT user_id, COUNT(*) as c FROM subjects GROUP BY user_id");
while($row = $res->fetch_assoc()) echo "User " . ($row['user_id'] ?: 'NULL') . ": " . $row['c'] . "\n";
?>
