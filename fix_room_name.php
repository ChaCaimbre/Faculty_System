<?php
require_once 'config.php';
$db = getDB();

$db->query("UPDATE rooms SET name = 'COMPLAB4' WHERE name = 'COMLAB4'");
echo "Renamed COMLAB4 to COMPLAB4" . PHP_EOL;
?>
