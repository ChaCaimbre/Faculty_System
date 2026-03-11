<?php
require_once 'config.php';
$db = getDB();

// 1. Find or Create COMPLAB5 (CON 103) - note: room name uses COMPLAB
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB5 (CON 103)'");
if ($room_res->num_rows == 0) {
    $db->query("INSERT INTO rooms (name) VALUES ('COMLAB5 (CON 103)')");
    $rid = $db->insert_id;
    echo "Created room COMLAB5 (CON 103)." . PHP_EOL;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// 2. Clear existing schedules
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for COMLAB5 (CON 103)." . PHP_EOL;

// 3. Helper: ensure subject exists
function ensureSubject($db, $code, $name)
{
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$code', '" . $db->real_escape_string($name) . "', 3)");
        echo "Inserted subject: $code" . PHP_EOL;
    }
}

// Ensure all subjects exist
ensureSubject($db, 'GE-113', 'Living in the IT Era (Elective)');
ensureSubject($db, 'IT-124', 'Quantitative Methods with Simulations and Modeling');
ensureSubject($db, 'SPT-104', 'SPT-104');
ensureSubject($db, 'IT-129L', 'System Administration and Maintenance (Laboratory)');
ensureSubject($db, 'IT-126', 'Social and Professional Issues');
ensureSubject($db, 'IT-128L', 'Capstone Project I (Laboratory)');
ensureSubject($db, 'SPT-111', 'SPT-111');
ensureSubject($db, 'LIS-106', 'LIS-106');
ensureSubject($db, 'SPT-110', 'SPT-110');
ensureSubject($db, 'LIS-112', 'LIS-112');
ensureSubject($db, 'GE-107', 'GE-107');
ensureSubject($db, 'TOUR-101', 'TOUR-101');

// 4. Helper: add schedule
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
    ['07:30:00', '09:00:00', 'GE-113', 'VERECIO', 'AS21'],
    ['09:00:00', '10:30:00', 'GE-113', 'TIQUEN', 'MH21'],
    ['10:30:00', '12:00:00', 'IT-124', 'APOLINAR', 'AI34'],
    ['13:00:00', '14:30:00', 'SPT-104', 'APOLINAR', 'AI21'],
    ['14:30:00', '16:00:00', 'IT-129L', 'TIBE', 'AI31'],
    ['16:00:00', '17:30:00', 'IT-126', 'TIBE', 'AI32'],
    // 17:30 - 19:00 VACANT (skipped)
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 6. Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-128L', 'AMAGO', 'AI36'],
    ['09:00:00', '10:30:00', 'IT-128L', 'AMAGO', 'AI35'],
    ['10:30:00', '12:00:00', 'SPT-111', 'NAVARRO', 'AI35'],
    ['13:00:00', '14:30:00', 'LIS-106', 'NAVARRO', 'AL21'],
    ['14:30:00', '16:00:00', 'SPT-110', 'NAVARRO', 'AL21'],
    // 16:00 - 17:30 VACANT (skipped)
    ['17:30:00', '19:00:00', 'IT-124', 'MEMORACION', 'AI35'],
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 7. Wednesday
$wed = [
    // 08:00 - 10:00 VACANT (skipped)
    ['10:00:00', '12:00:00', 'LIS-112', 'NAVARRO', 'AI31'],
    // 13:00 - 15:00 VACANT (skipped)
    ['15:00:00', '17:00:00', 'GE-107', 'NAVARRO', 'AL21'],
    ['17:00:00', '19:00:00', 'TOUR-101', 'ALMENARIO', 'MT12'],
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB5 (CON 103) Schedule Updated successfully!" . PHP_EOL;
?>
