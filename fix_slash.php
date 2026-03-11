<?php
require_once 'config.php';
$db = getDB();

$query = "UPDATE schedules SET section = REPLACE(section, '/', '') WHERE section LIKE '%/%'";
if ($db->query($query)) {
    echo "SUCCESS: Removed trailing slashes from sections. Rows affected: " . $db->affected_rows . PHP_EOL;
}
else {
    echo "ERROR: " . $db->error . PHP_EOL;
}
?>
