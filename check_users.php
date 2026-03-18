<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT id, username FROM users");
while($row = $res->fetch_assoc()){
    echo "{$row['id']}: {$row['username']}\n";
}
?>
