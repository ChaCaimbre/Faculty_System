<?php
require_once 'config.php';
$db = getDB();

// 1. Find or Create CHS (CON 101)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'CHS (CON 101)'");
if ($room_res->num_rows == 0) {
    $db->query("INSERT INTO rooms (name) VALUES ('CHS (CON 101)')");
    $rid = $db->insert_id;
    echo "Created room CHS (CON 101)." . PHP_EOL;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// 2. Clear existing schedules
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for CHS (CON 101)." . PHP_EOL;

// 3. Ensure subjects exist
function ensureSubject($db, $code, $name)
{
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$code', '" . $db->real_escape_string($name) . "', 3)");
        echo "Inserted subject: $code" . PHP_EOL;
    }
}

ensureSubject($db, 'GE-113', 'Living in the IT Era (Elective)');
ensureSubject($db, 'IT-130', 'Computer Hardware Repair and Maintenance');
ensureSubject($db, 'IT-130L', 'Computer Hardware Repair and Maintenance (Laboratory)');
ensureSubject($db, 'IT-129', 'System Administration and Maintenance');
ensureSubject($db, 'IT-129L', 'System Administration and Maintenance (Laboratory)');

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
    ['07:30:00', '09:00:00', 'GE-113', 'TIQUEN', 'MH24'],
    ['09:00:00', '10:30:00', 'IT-130', 'TURCO', 'AI11'],
    ['10:30:00', '12:00:00', 'IT-130L', 'TURCO', 'AI15'],
    ['13:00:00', '14:30:00', 'IT-129', 'TURCO', 'AI34'],
    ['14:30:00', '16:00:00', 'IT-129L', 'TURCO', 'AI34'],
    // 16:00-17:30 VACANT (skipped)
    ['17:30:00', '19:00:00', 'IT-130L', 'VILLAFUERTE', 'AI14'],
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 6. Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-130', 'DURANGO', 'AI12'],
    ['09:00:00', '10:30:00', 'IT-129L', 'TURCO', 'AI32'],
    ['10:30:00', '12:00:00', 'IT-130L', 'DURANGO', 'AI12'],
    // 13:00-14:30 VACANT (skipped)
    ['14:30:00', '16:00:00', 'IT-130L', 'DURANGO', 'AI13'],
    // 16:00-17:30 VACANT (skipped)
    // 17:30-19:00 VACANT (skipped)
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 7. Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-130', 'TURCO', 'AI11'],
    ['10:00:00', '12:00:00', 'IT-129', 'TURCO', 'AI32'],
    ['13:00:00', '15:00:00', 'IT-130', 'DURANGO', 'AI13'],
    ['15:00:00', '17:00:00', 'IT-130', 'TURCO', 'AI15'],
    ['17:00:00', '19:00:00', 'IT-130', 'VILLAFUERTE', 'AI14'],
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "CHS (CON 101) Schedule Updated successfully!" . PHP_EOL;
?>
