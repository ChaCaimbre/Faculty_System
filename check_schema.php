<?php
require_once 'config.php';
$db = getDB();

$tables = ['faculty', 'subjects', 'rooms', 'schedules'];
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res = $db->query("DESCRIBE $table");
    while ($row = $res->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }
}
?>
