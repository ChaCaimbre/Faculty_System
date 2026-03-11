<?php
require_once 'config.php';
$db = getDB();

// Ensure all faculty from the image exist
$facultyNames = [
    'AMAGO', 'DURANGO', 'TEO', 'ABELARDE', 'MUNOZ', 'TUVILLA', 'CASTILLO',
    'ABELA', 'MURILLO', 'VILLAFLOR', 'VILLAVERDE', 'CABREZA', 'CALLEJO',
    'SALAMENTE', 'ENTRE', 'MORITO', 'TABURADO', 'GARCIA', 'BAUTISTA', 'VILLAFUERTE'
];

foreach ($facultyNames as $name) {
    $res = $db->query("SELECT id FROM faculty WHERE name = '$name'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO faculty (name) VALUES ('$name')");
    }
}

// Clear old schedules
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE schedules");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

$subjects_res = $db->query("SELECT id, code FROM subjects");
$subjectsMap = [];
while ($row = $subjects_res->fetch_assoc())
    $subjectsMap[$row['code']] = $row['id'];

$faculty_res = $db->query("SELECT id, name FROM faculty");
$facultyMap = [];
while ($row = $faculty_res->fetch_assoc())
    $facultyMap[$row['name']] = $row['id'];

$rooms_res = $db->query("SELECT id, name FROM rooms");
$roomsMap = [];
while ($row = $rooms_res->fetch_assoc())
    $roomsMap[$row['name']] = $row['id'];

function addSched($db, $room, $day, $start, $end, $subCode, $facName, $roomsMap, $subjectsMap, $facultyMap)
{
    if (!isset($roomsMap[$room]) || !isset($subjectsMap[$subCode]) || !isset($facultyMap[$facName]))
        return;
    $rid = $roomsMap[$room];
    $sid = $subjectsMap[$subCode];
    $fid = $facultyMap[$facName];
    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $fid, $sid, $rid, $day, $start, $end);
    $stmt->execute();
}

$scheds = [
    // format: [Lab, Day, Start, End, Subject, Faculty]

    // Pattern 1: Mon/Thu and Tue/Fri (90 mins)
    // COMPLAB1 - Mon/Thu
    ['COMPLAB1', 'Monday', '07:30:00', '09:00:00', 'IT-128', 'AMAGO'],
    ['COMPLAB1', 'Thursday', '07:30:00', '09:00:00', 'IT-128', 'AMAGO'],
    ['COMPLAB1', 'Monday', '09:00:00', '10:30:00', 'IT-103', 'DURANGO'],
    ['COMPLAB1', 'Thursday', '09:00:00', '10:30:00', 'IT-103', 'DURANGO'],
    ['COMPLAB1', 'Monday', '10:30:00', '12:00:00', 'IT-121', 'AMAGO'],
    ['COMPLAB1', 'Thursday', '10:30:00', '12:00:00', 'IT-121', 'AMAGO'],
    ['COMPLAB1', 'Monday', '13:00:00', '14:30:00', 'IT-125', 'DURANGO'],
    ['COMPLAB1', 'Thursday', '13:00:00', '14:30:00', 'IT-125', 'DURANGO'],
    ['COMPLAB1', 'Monday', '14:30:00', '16:00:00', 'IT-113', 'AMAGO'],
    ['COMPLAB1', 'Thursday', '14:30:00', '16:00:00', 'IT-113', 'AMAGO'],
    ['COMPLAB1', 'Monday', '16:00:00', '17:30:00', 'IT-129', 'AMAGO'],
    ['COMPLAB1', 'Thursday', '16:00:00', '17:30:00', 'IT-129', 'AMAGO'],

    // COMPLAB1 - Tue/Fri
    ['COMPLAB1', 'Tuesday', '07:30:00', '09:00:00', 'IT-103L', 'DURANGO'],
    ['COMPLAB1', 'Friday', '07:30:00', '09:00:00', 'IT-103L', 'DURANGO'],
    ['COMPLAB1', 'Tuesday', '09:00:00', '10:30:00', 'IT-128L', 'AMAGO'],
    ['COMPLAB1', 'Friday', '09:00:00', '10:30:00', 'IT-128L', 'AMAGO'],
    ['COMPLAB1', 'Tuesday', '10:30:00', '12:00:00', 'IT-121L', 'AMAGO'],
    ['COMPLAB1', 'Friday', '10:30:00', '12:00:00', 'IT-121L', 'AMAGO'],
    ['COMPLAB1', 'Tuesday', '13:00:00', '14:30:00', 'IT-125L', 'DURANGO'],
    ['COMPLAB1', 'Friday', '13:00:00', '14:30:00', 'IT-125L', 'DURANGO'],
    ['COMPLAB1', 'Tuesday', '14:30:00', '16:00:00', 'IT-113L', 'AMAGO'],
    ['COMPLAB1', 'Friday', '14:30:00', '16:00:00', 'IT-113L', 'AMAGO'],
    ['COMPLAB1', 'Tuesday', '16:00:00', '17:30:00', 'IT-129L', 'AMAGO'],
    ['COMPLAB1', 'Friday', '16:00:00', '17:30:00', 'IT-129L', 'AMAGO'],

    // Pattern 2: Wednesday and Saturday (2 hours)
    // COMPLAB1 - Wed
    ['COMPLAB1', 'Wednesday', '08:00:00', '10:00:00', 'IT-105L', 'DURANGO'],
    ['COMPLAB1', 'Wednesday', '10:00:00', '12:00:00', 'IT-105', 'DURANGO'],
    ['COMPLAB1', 'Wednesday', '13:00:00', '15:00:00', 'IT-124', 'DURANGO'],
    ['COMPLAB1', 'Wednesday', '15:00:00', '17:00:00', 'IT-126', 'DURANGO'],

    // COMPLAB1 - Sat
    ['COMPLAB1', 'Saturday', '08:00:00', '10:00:00', 'IT-131', 'TUVILLA'],
    ['COMPLAB1', 'Saturday', '10:00:00', '12:00:00', 'IT-131', 'TUVILLA'],

    // Continuing with COMPLAB2 pattern...
    ['COMPLAB2', 'Monday', '07:30:00', '09:00:00', 'IT-112', 'MUNOZ'],
    ['COMPLAB2', 'Thursday', '07:30:00', '09:00:00', 'IT-112', 'MUNOZ']
];

foreach ($scheds as $slot) {
    addSched($db, $slot[0], $slot[1], $slot[2], $slot[3], $slot[4], $slot[5], $roomsMap, $subjectsMap, $facultyMap);
}

echo "Final schedule seeded with correct room names!";
?>
