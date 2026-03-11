<?php
require_once 'config.php';
$db = getDB();

$res = $db->query("SHOW COLUMNS FROM schedules LIKE 'section'");
if ($res->num_rows == 0) {
    $db->query("ALTER TABLE schedules ADD COLUMN section VARCHAR(50) AFTER day");
    echo "Added 'section' column to 'schedules' table." . PHP_EOL;
}
else {
    echo "'section' column already exists." . PHP_EOL;
}
?>
