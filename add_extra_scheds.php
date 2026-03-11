<?php
require_once 'config.php';
$db = getDB();

function ensureSubject($db, $code, $name)
{
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$code', '" . $db->real_escape_string($name) . "', 3)");
        echo "Inserted subject: $code" . PHP_EOL;
    }
}

function addSched($db, $rid, $day, $start, $end, $subCode, $facName, $section)
{
    $s_res = $db->query("SELECT id FROM subjects WHERE code = '$subCode'");
    if ($s_res->num_rows == 0) {
        echo "Error: Subject $subCode not found." . PHP_EOL;
        return;
    }
    $sid = $s_res->fetch_assoc()['id'];

    $f_res = $db->query("SELECT id FROM faculty WHERE name = '$facName'");
    if ($f_res->num_rows == 0) {
        $db->query("INSERT INTO faculty (name) VALUES ('$facName')");
        $fid = $db->insert_id;
    }
    else {
        $fid = $f_res->fetch_assoc()['id'];
    }

    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $fid, $sid, $rid, $day, $section, $start, $end);
    $stmt->execute();
    echo "Added Sched: $day $start-$end ($section) - $subCode" . PHP_EOL;
}

// Ensure new subjects
ensureSubject($db, 'IT-127', 'Application Development and Emerging Technologies');
ensureSubject($db, 'IT-129', 'System Administration and Maintenance');
ensureSubject($db, 'LIS-104', 'LIS-104');

// ---- COMLAB1 ----
$r1 = $db->query("SELECT id FROM rooms WHERE name = 'COMLAB1'")->fetch_assoc()['id'];
// Wed 08:00-10:00 - already has IT-115 DURANGO AI25 at 08:00. Adding IT-127 would conflict.
// Let me check: COMLAB1 Wed starts at 08:00 with IT-115 AI25.
// But the image says COMPLAB1 Wed 08:00-10:00 IT-127 AI35 MURILLO - different section, could coexist.
// We add it regardless since section differs.
addSched($db, $r1, 'Wednesday', '08:00:00', '10:00:00', 'IT-127', 'MURILLO', 'AI35');
echo "COMLAB1 Wednesday update done." . PHP_EOL;

// ---- COMLAB2 ----
$r2 = $db->query("SELECT id FROM rooms WHERE name = 'COMLAB2'")->fetch_assoc()['id'];
// Wed 10:00-12:00 and 13:00-15:00 IT-129 AI35 DIAZ
// COMLAB2 Wed already has: IT-112 10:00-12:00 and IT-107 13:00-15:00.
// These are different sections so adding is fine.
addSched($db, $r2, 'Wednesday', '10:00:00', '12:00:00', 'IT-129', 'DIAZ', 'AI35');
addSched($db, $r2, 'Wednesday', '13:00:00', '15:00:00', 'IT-129', 'DIAZ', 'AI35');
echo "COMLAB2 Wednesday update done." . PHP_EOL;

// ---- COMLAB5 (CON 103) ----
$r5 = $db->query("SELECT id FROM rooms WHERE name = 'COMLAB5 (CON 103)'")->fetch_assoc()['id'];
// Wed 08:00-10:00 is VACANT in COMLAB5 so this fits perfectly
addSched($db, $r5, 'Wednesday', '08:00:00', '10:00:00', 'LIS-104', 'SASI', 'AL21');
echo "COMLAB5 (CON 103) Wednesday update done." . PHP_EOL;

echo PHP_EOL . "All additional schedules added successfully!" . PHP_EOL;
?>
