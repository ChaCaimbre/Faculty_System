<?php
require_once 'config.php';
$db = getDB();

echo "--- ROOMS ---" . PHP_EOL;
$res = $db->query("SELECT name FROM rooms");
while ($row = $res->fetch_assoc())
    echo $row['name'] . PHP_EOL;

echo "--- FACULTY ---" . PHP_EOL;
$res = $db->query("SELECT name FROM faculty");
while ($row = $res->fetch_assoc())
    echo $row['name'] . PHP_EOL;

echo "--- SUBJECTS (LIMIT 10) ---" . PHP_EOL;
$res = $db->query("SELECT code, name FROM subjects LIMIT 10");
while ($row = $res->fetch_assoc())
    echo $row['code'] . " - " . $row['name'] . PHP_EOL;
?>








