<?php
require_once 'config.php';
$db = getDB();
$u = 1;

$sc = $db->query("SELECT COUNT(*) as c FROM schedules WHERE user_id=$u")->fetch_assoc()['c'];
$fc = $db->query("SELECT COUNT(*) as c FROM faculty WHERE user_id=$u")->fetch_assoc()['c'];
$terms = $db->query("SELECT * FROM academic_terms")->fetch_all(MYSQLI_ASSOC);

echo "--- Status for User $u ---\n";
echo "Schedules: $sc\n";
echo "Faculty: $fc\n";
echo "Terms:\n";
foreach($terms as $t) {
    $tc = $db->query("SELECT COUNT(*) as c FROM schedules WHERE user_id=$u AND term_id={$t['id']}")->fetch_assoc()['c'];
    echo "- ID {$t['id']}: {$t['name']} (Schedules: $tc)\n";
}
?>
