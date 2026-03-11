<?php
require_once 'config.php';
$db = getDB();

// 1. Ensure extra subjects exist (TOUR-101L, GE-113) - Redundant as seed_subjects.php handles it, but safe to keep check logic IF they weren't in seed.
// We can skip the insert block since we just ran seed_subjects.php which includes them.

// 2. Find COMPLAB2 ID (room name in DB)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB2'");
if ($room_res->num_rows == 0) {
    die("Error: COMLAB2 not found.");
}
$rid = $room_res->fetch_assoc()['id'];

// 3. Clear existing schedules for COMLAB2
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for COMLAB2." . PHP_EOL;

// 4. Helper function
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

// 5. Define Schedules

// Monday & Thursday
$monThu = [
    ['07:30:00', '09:00:00', 'IT-107', 'GALBAN', 'AI31'],
    ['09:00:00', '10:30:00', 'IT-107L', 'GALBAN', 'AI31'],
    ['10:30:00', '12:00:00', 'IT-108L', 'CINCO', 'AI31'],
    ['13:00:00', '14:30:00', 'IT-105L', 'CABANGON', 'AI31'],
    ['14:30:00', '16:00:00', 'IT-107L', 'GALBAN', 'AI41'],
    ['16:00:00', '17:30:00', 'IT-107', 'GALBAN', 'AI41'],
    ['17:30:00', '19:00:00', 'TOUR-101L', 'MEMORACION', 'MT13']
];

foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-127L', 'MURILLO', 'AI31'],
    ['09:00:00', '10:30:00', 'IT-107L', 'GALBAN', 'AI15'],
    ['10:30:00', '12:00:00', 'IT-108L', 'DALAN', 'AI14'],
    ['13:00:00', '14:30:00', 'GE-113', 'QUISUMBING', 'SM31'], // Updated spelling
    ['14:30:00', '16:00:00', 'IT-115L', 'FERNANDEZ', 'AI24'],
    ['16:00:00', '17:30:00', 'IT-127L', 'LAGONOY', 'AI32'],
    ['17:30:00', '19:00:00', 'TOUR-101L', 'ALMENARIO', 'MT12']
];

foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-112', 'ORMENETA', 'AI22'],
    ['10:00:00', '12:00:00', 'IT-112', 'ORMENETA', 'AI23'],
    ['13:00:00', '15:00:00', 'IT-107', 'GALBAN', 'AI15'],
    ['15:00:00', '17:00:00', 'IT-127', 'LAGONOY', 'AI33'],
    ['17:00:00', '19:00:00', 'IT-127', 'CELESTIAL', 'AI31']
];

foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB2 Schedule Updated successfully!" . PHP_EOL;
?>
