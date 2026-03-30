<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT id, name, user_id FROM faculty WHERE name LIKE '%SASI%'");
while($row = $res->fetch_assoc()) echo "ID: {$row['id']}, Name: {$row['name']}, User: {$row['user_id']}\n";
?>
