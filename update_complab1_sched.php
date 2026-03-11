<?php
require_once 'config.php';
$db = getDB();

// Ensure all subjects from the schedules exist
$subjectData = [
    'IT-128' => "Capstone Project I",
    'IT-128L' => "Capstone Project I (Laboratory)",
    'IT-130L' => "Computer Hardware Repair and Maintenance (Laboratory)",
    'IT-115' => "Introduction to Human Computer Interaction",
    'IT-115L' => "Introduction to Human Computer Interaction (Laboratory)",
    'IT-105L' => "Mobile Development (Laboratory)",
    'IT-112L' => "Integrative Programming and Technologies Laboratory",
    'IT-122L' => "System Analysis and Design (Laboratory)",
    'IT-127L' => "Application Development and Emerging Technologies (Laboratory)",
    'IT-105' => "Mobile Development",
    'IT-124' => "Information Assurance and Security",
    'IT-126' => "Software Engineering"
];

foreach ($subjectData as $code => $name) {
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name) VALUES ('$code', '$name')");
        echo "Inserted Subject: $code" . PHP_EOL;
    }
    else {
        echo "Found Subject: $code" . PHP_EOL;
    }
}

// Find COMPLAB1 ID (note: room name has 'COMPLAB1' in DB)
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB1'");
if ($room_res->num_rows == 0) {
    die("Error: COMPLAB1 not found.");
}
$rid = $room_res->fetch_assoc()['id'];

// Clear Mon-Sat to remove Tuvilla but keep a clean slate for the rest
$db->query("DELETE FROM schedules WHERE room_id = $rid AND day IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')");

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
    echo "Added Sched: $day $start ($section)" . PHP_EOL;
}

// Mon/Thu
$monThu = [
    ['07:30:00', '09:00:00', 'IT-128', 'AMAGO', 'AI32'],
    ['09:00:00', '10:30:00', 'IT-128L', 'AMAGO', 'AI32'],
    ['10:30:00', '12:00:00', 'IT-130L', 'DALAN', 'AI11'],
    ['13:00:00', '14:30:00', 'IT-115', 'DURANGO', 'AI22'],
    ['14:30:00', '16:00:00', 'IT-115L', 'DURANGO', 'AI22'],
    ['16:00:00', '17:30:00', 'IT-105L', 'SAMPAYAN', 'AI34'],
    ['17:30:00', '19:00:00', 'IT-112L', 'COMBINIDO', 'AI24']
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Tue/Fri
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-128', 'FUNCION', 'AI31'],
    ['09:00:00', '10:30:00', 'IT-128L', 'FUNCION', 'AI31'],
    ['10:30:00', '12:00:00', 'IT-115L', 'FERNANDEZ', 'AI21'],
    ['14:30:00', '16:00:00', 'IT-112L', 'AMAGO', 'AI21'],
    ['16:00:00', '17:30:00', 'IT-105L', 'CABANGON', 'AI35'],
    ['17:30:00', '19:00:00', 'IT-127L', 'CELESTIAL', 'AI31']
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-115', 'DURANGO', 'AI25'],
    ['10:00:00', '12:00:00', 'IT-108', 'DALAN', 'AI11'],
    // 13:00 - 15:00 is VACANT
    ['15:00:00', '17:00:00', 'IT-107', 'TIQUEN', 'AI11'],
    ['17:00:00', '19:00:00', 'IT-112', 'COMBINIDO', 'AI24']
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB1 Schedule Fully Restored (Mon-Fri)!" . PHP_EOL;
?>
