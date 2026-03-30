<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT id, name, user_id FROM rooms WHERE name LIKE '%TBA%'");
while($row = $res->fetch_assoc()) echo "ID: {$row['id']}, Name: '{$row['name']}', User: {$row['user_id']}\n";
?>
