<?php
require_once 'config.php';
$db = getDB();
$queries = [
    "Total Schedules" => "SELECT COUNT(*) FROM schedules",
    "Schedules with valid Faculty" => "SELECT COUNT(*) FROM schedules s JOIN faculty f ON s.faculty_id = f.id",
    "Schedules with valid Room" => "SELECT COUNT(*) FROM schedules s JOIN rooms r ON s.room_id = r.id",
    "Schedules with valid Subject" => "SELECT COUNT(*) FROM schedules s JOIN subjects sub ON s.subject_id = sub.id",
    "Schedules with ALL valid Joins" => "SELECT COUNT(*) FROM schedules s JOIN faculty f ON s.faculty_id = f.id JOIN rooms r ON s.room_id = r.id JOIN subjects sub ON s.subject_id = sub.id"
];

foreach ($queries as $label => $q) {
    $r = $db->query($q);
    echo "$label: " . $r->fetch_row()[0] . "\n";
}
?>
