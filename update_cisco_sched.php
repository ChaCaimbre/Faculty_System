<?php
require_once 'config.php';
$db = getDB();

// 1. Find or Create CISCO (CON 102)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'CISCO (CON 102)'");
if ($room_res->num_rows == 0) {
    $db->query("INSERT INTO rooms (name) VALUES ('CISCO (CON 102)')");
    $rid = $db->insert_id;
    echo "Created room CISCO (CON 102)." . PHP_EOL;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// 2. Clear existing schedules
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for CISCO (CON 102)." . PHP_EOL;

// 3. Ensure subjects exist
function ensureSubject($db, $code, $name)
{
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$code', '" . $db->real_escape_string($name) . "', 3)");
        echo "Inserted subject: $code" . PHP_EOL;
    }
}

ensureSubject($db, 'IT-116L', 'IT-116L');
ensureSubject($db, 'IT-116', 'IT-116');
ensureSubject($db, 'IT-126', 'Social and Professional Issues');
ensureSubject($db, 'IT-105', 'Mobile Development');

// 4. Helper
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
    echo "Added Sched: $day $start ($section) - $subCode" . PHP_EOL;
}

// 5. Monday & Thursday
$monThu = [
    ['07:30:00', '09:00:00', 'IT-116L', 'QUISUMBING', 'AI21'],
    ['09:00:00', '10:30:00', 'IT-116', 'QUISUMBING', 'AI21'],
    ['10:30:00', '12:00:00', 'IT-126', 'TIQUEN', 'AI35'],
    // 13:00-19:00 VACANT (skipped)
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 6. Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-116L', 'QUISUMBING', 'AI23'],
    ['09:00:00', '10:30:00', 'IT-116', 'QUISUMBING', 'AI23'],
    ['10:30:00', '12:00:00', 'IT-116L', 'CINCO', 'AI22'],
    ['13:00:00', '14:30:00', 'IT-116L', 'CABANGON', 'AI25'],
    ['14:30:00', '16:00:00', 'IT-116L', 'CINCO', 'AI25'],
    ['16:00:00', '17:30:00', 'IT-116', 'CINCO', 'AI22'],
    // 17:30-19:00 VACANT (skipped)
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 7. Wednesday
$wed = [
    // 08:00-10:00 VACANT (skipped)
    ['10:00:00', '12:00:00', 'IT-105', 'CABANGON', 'AI35'],
    ['13:00:00', '15:00:00', 'IT-116', 'CINCO', 'AI25'],
    ['15:00:00', '17:00:00', 'IT-116', 'CABANGON', 'AI24'],
    // 17:00-19:00 VACANT (skipped)
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "CISCO (CON 102) Schedule Updated successfully!" . PHP_EOL;
?>
