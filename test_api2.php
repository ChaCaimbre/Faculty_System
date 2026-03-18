<?php
require_once 'config.php';
$uid = 2; // nikki
$term_id = 1;
$db = getDB();
$query = "SELECT s.* FROM schedules s WHERE s.user_id = $uid";
$result = $db->query($query);
echo "Count for user 2: " . $result->num_rows . "\n";
?>
