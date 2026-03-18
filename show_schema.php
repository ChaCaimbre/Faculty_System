<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SHOW CREATE TABLE subjects");
$row = $res->fetch_assoc();
echo $row['Create Table'];
?>
