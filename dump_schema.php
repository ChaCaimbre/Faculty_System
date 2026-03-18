<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SHOW CREATE TABLE subjects");
$row = $res->fetch_assoc();
file_put_contents('subjects_schema.txt', $row['Create Table']);
echo "Schema written.\n";
?>
