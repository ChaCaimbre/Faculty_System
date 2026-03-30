<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT user_id, count(*) as count FROM schedules GROUP BY user_id");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
