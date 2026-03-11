<?php
require_once 'config.php';
$db = getDB();

// Find COMPLAB4 room ID (name in DB)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB4'");
if ($room_res->num_rows == 0) {
    $db->query("INSERT INTO rooms (name) VALUES ('COMLAB4')");
    $rid = $db->insert_id;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// Clear existing schedules for COMLAB4
$db->query("DELETE FROM schedules WHERE room_id = $rid");

function addSched($db, $rid, $day, $start, $end, $subCode, $facName, $section)
{
    // Subject check
    $s_res = $db->query("SELECT id FROM subjects WHERE code = '$subCode'");
    if ($s_res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name) VALUES ('$subCode', '$subCode')");
        $sid = $db->insert_id;
    }
    else {
        $sid = $s_res->fetch_assoc()['id'];
    }

    // Faculty check
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
    echo "Added Sched: $day $start ($section) - $subCode [$facName]" . PHP_EOL;
}

// Mon/Thu
$monThu = [
    ['07:30:00', '09:00:00', 'IT-105L', 'CABANGON', 'AI33'],
    ['09:00:00', '10:30:00', 'IT-112L', 'ORMENETA', 'AI22'],
    ['10:30:00', '12:00:00', 'IT-112L', 'ORMENETA', 'AI23'],
    ['13:00:00', '14:30:00', 'IT-108L', 'ORMENETA', 'AI12'],
    ['14:30:00', '16:00:00', 'IT-120L', 'DALAN', 'AI21']
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Tue/Fri
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-120', 'TIBE', 'AI24'],
    ['09:00:00', '10:30:00', 'IT-120L', 'TIBE', 'AI24'],
    ['10:30:00', '12:00:00', 'GE-113', 'GALBAN', 'AS22'],
    ['13:00:00', '14:30:00', 'IT-120L', 'TIBE', 'AI22'],
    ['14:30:00', '16:00:00', 'IT-120', 'TIBE', 'AI22']
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-112', 'AMAGO', 'AI21'],
    ['10:00:00', '12:00:00', 'IT-112', 'AMAGO', 'AI25'],
    ['13:00:00', '15:00:00', 'IT-127', 'LAGONOY', 'AI32'],
    ['15:00:00', '17:00:00', 'IT-105', 'SAMPAYAN', 'AI34'],
    ['17:00:00', '19:00:00', 'IT-127', 'MURILLO', 'AI34']
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB4 schedule populated successfully." . PHP_EOL;
?>
