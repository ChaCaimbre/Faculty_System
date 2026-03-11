<?php
require_once 'config.php';
$db = getDB();
$db->query("UPDATE rooms SET name = REPLACE(name, 'COMPLAB', 'COMLAB')");
echo "Updated " . $db->affected_rows . " rooms to COMLAB.";
?>
