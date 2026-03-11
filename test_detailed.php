<?php
require_once 'config.php';
$db = getDB();
$queries = [
    "Schedules" => "SELECT * FROM schedules LIMIT 1",
    "Faculty" => "SELECT * FROM faculty LIMIT 1",
    "Rooms" => "SELECT * FROM rooms LIMIT 1",
    "Subjects" => "SELECT * FROM subjects LIMIT 1"
];

foreach ($queries as $label => $q) {
    echo "--- $label ---\n";
    $r = $db->query($q);
    if ($r && $row = $r->fetch_assoc()) {
        print_r($row);
    } else {
        echo "No data or Error: " . $db->error . "\n";
    }
}
?>
