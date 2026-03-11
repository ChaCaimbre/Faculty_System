<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT name FROM faculty");
$names = [];
while ($row = $res->fetch_assoc())
    $names[] = $row['name'];
echo json_encode($names);
?>
