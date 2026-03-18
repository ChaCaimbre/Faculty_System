<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT * FROM academic_terms");
while($row = $res->fetch_assoc()) print_r($row);
?>
