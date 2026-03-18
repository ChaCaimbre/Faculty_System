<?php
require_once 'config.php';
$db = getDB();
$sql = "SELECT s.user_id, s.term_id, COUNT(*) as c 
        FROM schedules s 
        GROUP BY s.user_id, s.term_id";
$res = $db->query($sql);
while($row = $res->fetch_assoc()){
    echo "User {$row['user_id']} Term {$row['term_id']}: {$row['c']}\n";
}
?>
