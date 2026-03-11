<?php
require_once 'config.php';
$db = getDB();

// 1. Find COMPLAB3 ID (room name in DB)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB3'");
if ($room_res->num_rows == 0) {
    // Attempt to create if not exists, though usually it should
    $db->query("INSERT INTO rooms (name) VALUES ('COMLAB3')");
    $rid = $db->insert_id;
    echo "Created room COMLAB3." . PHP_EOL;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// 2. Clear existing schedules for COMLAB3
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for COMLAB3." . PHP_EOL;

// 3. Helper function
function addSched($db, $rid, $day, $start, $end, $subCode, $facName, $section)
{
    // Check subject
    $s_res = $db->query("SELECT id FROM subjects WHERE code = '$subCode'");
    if ($s_res->num_rows == 0) {
        // Try simple insert for now if missing, using code as name placeholder to avoid breaking
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$subCode', '$subCode', 3)");
        $sid = $db->insert_id;
        echo "Inserted missing subject: $subCode" . PHP_EOL;
    }
    else {
        $sid = $s_res->fetch_assoc()['id'];
    }

    // Check faculty
    $f_res = $db->query("SELECT id FROM faculty WHERE name = '$facName'");
    if ($f_res->num_rows == 0) {
        $db->query("INSERT INTO faculty (name) VALUES ('$facName')");
        $fid = $db->insert_id;
    }
    else {
        $fid = $f_res->fetch_assoc()['id'];
    }

    // Insert schedule
    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $fid, $sid, $rid, $day, $section, $start, $end);
    $stmt->execute();
    echo "Added Sched: $day $start ($section) - $subCode" . PHP_EOL;
}

// 4. Define Schedules

// Monday & Thursday
$monThu = [
    ['07:30:00', '09:00:00', 'IT-128', 'FUNCION', 'AI31'], // FUNCTION -> FUNCION
    ['09:00:00', '10:30:00', 'IT-128L', 'FUNCION', 'AI34'],
    ['10:30:00', '12:00:00', 'IT-105L', 'CABANGON', 'AI32'],
    ['13:00:00', '14:30:00', 'IT-112L', 'AMAGO', 'AI25'],
    ['14:30:00', '16:00:00', 'IT-105', 'CABANGON', 'AI32'],
    ['16:00:00', '17:30:00', 'IT-127L', 'LAGONOY', 'AI33'],
    ['17:30:00', '19:00:00', 'IT-127L', 'MURILLO', 'AI35']
];

foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-107L', 'TIQUEN', 'AI11'],
    ['09:00:00', '10:30:00', 'IT-107L', 'TIQUEN', 'AI12'],
    ['10:30:00', '12:00:00', 'IT-124', 'FUNCION', 'AI33'],
    ['13:00:00', '14:30:00', 'IT-108L', 'ORMENETA', 'AI15'],
    ['14:30:00', '16:00:00', 'IT-108', 'ORMENETA', 'AI15'],
    ['16:00:00', '17:30:00', 'IT-115L', 'FERNANDEZ', 'AI23']
    // 17:30 - 19:00 VACANT (skipped)
];

foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-108', 'CINCO', 'AI31'],
    ['10:00:00', '12:00:00', 'IT-107', 'TIQUEN', 'AI12'],
    ['13:00:00', '15:00:00', 'IT-108', 'DALAN', 'AI14'],
    ['15:00:00', '17:00:00', 'IT-129', 'DABLEO', 'AI33']
    // 17:00 - 19:00 VACANT (skipped)
];

foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB3 Schedule Updated successfully!" . PHP_EOL;
?>
