<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT * FROM academic_terms");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Active: {$row['is_active']}\n";
}
?>
