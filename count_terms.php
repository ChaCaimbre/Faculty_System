<?php
require_once 'config.php';
$db = getDB();
foreach ([1, 2, 3] as $tid) {
    $sc = $db->query("SELECT COUNT(*) as c FROM schedules WHERE term_id=$tid")->fetch_assoc()['c'];
    $subc = $db->query("SELECT COUNT(*) as c FROM subjects WHERE term_id=$tid")->fetch_assoc()['c'];
    echo "Term $tid: Schedules: $sc, Subjects: $subc\n";
}
?>
