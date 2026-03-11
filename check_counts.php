<?php
require_once 'config.php';
$db = getDB();
foreach(['faculty','subjects','rooms','schedules'] as $t) {
    $r = $db->query("SELECT COUNT(*) as c FROM $t");
    echo "$t: " . $r->fetch_assoc()['c'] . "\n";
}
?>
