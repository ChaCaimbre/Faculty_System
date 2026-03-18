<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT code, name FROM subjects ORDER BY code");
while($row = $res->fetch_assoc()){
    echo "{$row['code']}: {$row['name']}\n";
}
?>
