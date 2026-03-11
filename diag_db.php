<?php
require_once 'config.php';
$db = getDB();

echo "--- FACULTY ---\n";
$res = $db->query("SELECT name FROM faculty");
while ($row = $res->fetch_assoc())
    echo $row['name'] . "\n";

echo "\n--- SUBJECTS ---\n";
$res = $db->query("SELECT code FROM subjects");
while ($row = $res->fetch_assoc())
    echo $row['code'] . "\n";

echo "\n--- ROOMS ---\n";
$res = $db->query("SELECT name FROM rooms");
while ($row = $res->fetch_assoc())
    echo $row['name'] . "\n";
?>
