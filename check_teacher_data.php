<?php
require_once 'config.php';
$db = getDB();

$res = $db->query("SELECT f.name as teacher, sub.code as subject, s.section 
                  FROM schedules s 
                  JOIN faculty f ON s.faculty_id = f.id 
                  JOIN subjects sub ON s.subject_id = sub.id 
                  ORDER BY f.name, sub.code, s.section");

$data = [];
while ($row = $res->fetch_assoc()) {
    $t = $row['teacher'];
    $s = $row['subject'];
    $sec = $row['section'];
    if (!isset($data[$t]))
        $data[$t] = [];
    if (!isset($data[$t][$s]))
        $data[$t][$s] = [];
    $data[$t][$s][] = $sec;
}

foreach ($data as $teacher => $subjects) {
    echo "[$teacher]\n";
    foreach ($subjects as $sub => $sections) {
        $secs = array_unique($sections);
        sort($secs);
        echo "  $sub: " . implode(", ", $secs) . "\n";
    }
}
?>
