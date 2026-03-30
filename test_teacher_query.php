<?php
require_once 'config.php';
$db = getDB();
$uid = 1;
$term_id = 1;

$query = "SELECT f.name as faculty_name, f.designated_campus, s.id, s.day, s.start_time, s.end_time, s.section,
                 r.name as room_name, sub.code as subject_code, sub.name as subject_name 
          FROM faculty f
          LEFT JOIN schedules s ON f.id = s.faculty_id AND s.term_id = $term_id
          LEFT JOIN rooms r      ON s.room_id    = r.id
          LEFT JOIN subjects sub ON s.subject_id = sub.id
          WHERE f.user_id = $uid
          ORDER BY f.name, s.day, s.start_time ASC";

$result = $db->query($query);
$schedules = $result->fetch_all(MYSQLI_ASSOC);

$grouped = [];
foreach ($schedules as $s) {
    $teacher = $s['faculty_name'];
    if (!isset($grouped[$teacher])) $grouped[$teacher] = [];
    $grouped[$teacher][] = $s;
}

echo "Check for Almenario in Term ID $term_id for User $uid:\n";
if (isset($grouped['ALMENARIO'])) {
    echo "Found " . count($grouped['ALMENARIO']) . " rows for ALMENARIO.\n";
    foreach($grouped['ALMENARIO'] as $row) {
        echo "- Subject: " . ($row['subject_code'] ?? 'NULL') . ", ID: " . ($row['id'] ?? 'NULL') . "\n";
    }
} else {
    echo "ALMENARIO not found in grouped results.\n";
    // Check faculty listing 
    $f = $db->query("SELECT * FROM faculty WHERE name LIKE '%ALMENARIO%' AND user_id=$uid")->fetch_assoc();
    if($f) echo "Faculty record for ALMENARIO exists with ID {$f['id']}\n";
    else echo "Faculty record for ALMENARIO NOT found for User $uid\n";
}
?>
