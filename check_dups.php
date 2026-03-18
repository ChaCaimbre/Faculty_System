<?php
require_once 'config.php';
$db = getDB();
$res = $db->query("SELECT code, COUNT(*) as c FROM subjects GROUP BY code HAVING c > 1");
while($row = $res->fetch_assoc()){
    echo "Duplicate code: {$row['code']} (Count: {$row['c']})\n";
}
?>
