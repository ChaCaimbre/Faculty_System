<?php
require_once 'config.php';
$db = getDB();
echo "Terms with schedules:\n";
$res = $db->query("SELECT DISTINCT term_id FROM schedules");
while ($row = $res->fetch_assoc()) {
    $tid = $row['term_id'];
    $termRes = $db->query("SELECT name FROM academic_terms WHERE id = $tid");
    $termName = ($termRes->num_rows > 0) ? $termRes->fetch_assoc()['name'] : 'Unknown';
    $countRes = $db->query("SELECT count(*) as c FROM schedules WHERE term_id = $tid");
    $count = $countRes->fetch_assoc()['c'];
    echo "ID: $tid | Name: $termName | Schedules: $count\n";
}
echo "\nAcademic Terms for User 1:\n";
$res = $db->query("SELECT id, name, is_active FROM academic_terms WHERE user_id = 1");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Active: " . $row['is_active'] . "\n";
}
?>
