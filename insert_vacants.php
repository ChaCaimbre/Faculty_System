<?php
require_once 'config.php';
$db = getDB();

// 1. Ensure a VACANT subject and faculty exist as placeholders
$db->query("INSERT IGNORE INTO subjects (code, name, units) VALUES ('VACANT', 'Vacant Period', 0)");
$sid_res = $db->query("SELECT id FROM subjects WHERE code = 'VACANT'");
$vacantSubjectId = $sid_res->fetch_assoc()['id'];

$db->query("INSERT INTO faculty (name) SELECT 'VACANT' WHERE NOT EXISTS (SELECT 1 FROM faculty WHERE name = 'VACANT')");
$fid_res = $db->query("SELECT id FROM faculty WHERE name = 'VACANT'");
$vacantFacultyId = $fid_res->fetch_assoc()['id'];

echo "VACANT subject ID: $vacantSubjectId, faculty ID: $vacantFacultyId" . PHP_EOL;

// Helper to get room id
function getRoomId($db, $name)
{
    $res = $db->query("SELECT id FROM rooms WHERE name = '$name'");
    if ($res->num_rows == 0) {
        echo "Room not found: $name" . PHP_EOL;
        return null;
    }
    return $res->fetch_assoc()['id'];
}

// Helper to add a VACANT slot
function addVacant($db, $roomId, $day, $start, $end, $vacantSubjectId, $vacantFacultyId)
{
    if (!$roomId)
        return;
    $section = 'VACANT';
    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $vacantFacultyId, $vacantSubjectId, $roomId, $day, $section, $start, $end);
    $stmt->execute();
    echo "Added VACANT: $day $start-$end in room $roomId" . PHP_EOL;
}

// ---- COMLAB1 ----
$r = getRoomId($db, 'COMLAB1');
// Delete existing VACANT entries first to avoid duplicates
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Tuesday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Wednesday', '13:00:00', '15:00:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB1 done." . PHP_EOL;

// ---- COMLAB2 (no vacant listed) ----
echo "COMLAB2 - no vacant periods to add." . PHP_EOL;

// ---- COMLAB3 ----
$r = getRoomId($db, 'COMLAB3');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Tuesday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB3 done." . PHP_EOL;

// ---- COMLAB4 ----
$r = getRoomId($db, 'COMLAB4');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Monday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Thursday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Tuesday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB4 done." . PHP_EOL;

// ---- COMLAB5 (CON 103) ----
$r = getRoomId($db, 'COMLAB5 (CON 103)');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Tuesday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB5 (CON 103) done." . PHP_EOL;

// ---- COMLAB6 (CON 104) ----
$r = getRoomId($db, 'COMLAB6 (CON 104)');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Tuesday', '10:30:00', '12:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '10:30:00', '12:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Monday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Thursday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Tuesday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB6 (CON 104) done." . PHP_EOL;

// ---- COMLAB7 (CON 105) ----
$r = getRoomId($db, 'COMLAB7 (CON 105)');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Monday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Thursday', '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Tuesday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
echo "COMLAB7 (CON 105) done." . PHP_EOL;

// ---- CHS (CON 101) ----
$r = getRoomId($db, 'CHS (CON 101)');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
addVacant($db, $r, 'Tuesday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Tuesday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
echo "CHS (CON 101) done." . PHP_EOL;

// ---- CISCO (CON 102) ----
$r = getRoomId($db, 'CISCO (CON 102)');
$db->query("DELETE FROM schedules WHERE room_id = $r AND section = 'VACANT'");
// Mon/Thu 13:00-19:00 (split into 1.5hr blocks)
foreach (['Monday', 'Thursday'] as $day) {
    addVacant($db, $r, $day, '13:00:00', '14:30:00', $vacantSubjectId, $vacantFacultyId);
    addVacant($db, $r, $day, '14:30:00', '16:00:00', $vacantSubjectId, $vacantFacultyId);
    addVacant($db, $r, $day, '16:00:00', '17:30:00', $vacantSubjectId, $vacantFacultyId);
    addVacant($db, $r, $day, '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
}
addVacant($db, $r, 'Tuesday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
addVacant($db, $r, 'Friday', '17:30:00', '19:00:00', $vacantSubjectId, $vacantFacultyId);
echo "CISCO (CON 102) done." . PHP_EOL;

echo PHP_EOL . "All VACANT slots inserted successfully!" . PHP_EOL;
?>
