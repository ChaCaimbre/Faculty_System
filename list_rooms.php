<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT name FROM rooms");
while ($row = $res->fetch_assoc()) {
    echo $row['name'] . PHP_EOL;
}
?>
