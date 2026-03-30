<?php
require_once 'config.php';
$db = getDB();
$uid = 1;
$term_id = 1;

$query = "SELECT f.name as faculty_name, f.id as fac_id, s.id as sched_id, s.term_id, sub.code as subject_code
          FROM faculty f
          LEFT JOIN schedules s ON f.id = s.faculty_id AND s.term_id = $term_id
          LEFT JOIN subjects sub ON s.subject_id = sub.id
          WHERE f.user_id = $uid
          ORDER BY f.name LIMIT 20";

$result = $db->query($query);
while($row = $result->fetch_assoc()) {
    echo "Fac: '{$row['faculty_name']}' (ID: {$row['fac_id']}), Sched ID: " . ($row['sched_id'] ?? 'NULL') . ", Subject: " . ($row['subject_code'] ?? '---') . "\n";
}
?>
