<?php
require_once 'config.php';
$db = getDB();

// 1. Find or Create COMPLAB6 (CON 104) - room name in DB
$room_res = $db->query("SELECT id FROM rooms WHERE name = 'COMPLAB6 (CON 104)'");
if ($room_res->num_rows == 0) {
    $db->query("INSERT INTO rooms (name) VALUES ('COMLAB6 (CON 104)')");
    $rid = $db->insert_id;
    echo "Created room COMLAB6 (CON 104)." . PHP_EOL;
}
else {
    $rid = $room_res->fetch_assoc()['id'];
}

// 2. Clear existing schedules
$db->query("DELETE FROM schedules WHERE room_id = $rid");
echo "Cleared all schedules for COMLAB6 (CON 104)." . PHP_EOL;

// 3. Ensure subjects exist
function ensureSubject($db, $code, $name)
{
    $res = $db->query("SELECT id FROM subjects WHERE code = '$code'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, units) VALUES ('$code', '" . $db->real_escape_string($name) . "', 3)");
        echo "Inserted subject: $code" . PHP_EOL;
    }
}

ensureSubject($db, 'IT-104', 'IT-104');
ensureSubject($db, 'IT-124', 'Quantitative Methods with Simulations and Modeling');
ensureSubject($db, 'GE-113', 'Living in the IT Era (Elective)');
ensureSubject($db, 'IT-134', 'IT-134');
ensureSubject($db, 'IT-120L', 'Geographic Information Systems (Laboratory)');
ensureSubject($db, 'IT-120', 'Geographic Information Systems');
ensureSubject($db, 'IT-115', 'Introduction to Human Computer Interaction');
ensureSubject($db, 'IT-128', 'Capstone Project I');
ensureSubject($db, 'TOUR-101', 'TOUR-101');

// 4. Add schedule helper
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
    ['07:30:00', '09:00:00', 'IT-104', 'APOLINAR', 'AI11'],
    ['09:00:00', '10:30:00', 'IT-104', 'APOLINAR', 'AI12'],
    ['10:30:00', '12:00:00', 'IT-124', 'LAURENTE', 'AI14'],
    ['13:00:00', '14:30:00', 'IT-104', 'SAMPAYAN', 'AI13'],
    ['14:30:00', '16:00:00', 'IT-104', 'SAMPAYAN', 'AI15'],
    // 16:00-17:30 VACANT (skipped)
    ['17:30:00', '19:00:00', 'GE-113', 'NICOLAS', 'MH22'],
];
foreach ($monThu as $s) {
    addSched($db, $rid, 'Monday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Thursday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 6. Tuesday & Friday
$tueFri = [
    ['07:30:00', '09:00:00', 'IT-134', 'LAURENTE', 'AI41'],
    ['09:00:00', '10:30:00', 'IT-134', 'LAURENTE', 'AI41'],
    // 10:30-12:00 VACANT (skipped)
    ['13:00:00', '14:30:00', 'IT-120L', 'DALAN', 'AI23'],
    ['14:30:00', '16:00:00', 'IT-120', 'DALAN', 'AI23'],
    // 16:00-17:30 VACANT (skipped)
    ['17:30:00', '19:00:00', 'GE-113', 'NICOLAS', 'MH23'],
];
foreach ($tueFri as $s) {
    addSched($db, $rid, 'Tuesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
    addSched($db, $rid, 'Friday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

// 7. Wednesday
$wed = [
    ['08:00:00', '10:00:00', 'IT-115', 'FERNANDEZ', 'AI24'],
    ['10:00:00', '12:00:00', 'IT-128', 'CALUZA', 'AI33'],
    ['13:00:00', '15:00:00', 'IT-115', 'FERNANDEZ', 'AI23'],
    ['15:00:00', '17:00:00', 'IT-115', 'FERNANDEZ', 'AI21'],
    ['17:00:00', '19:00:00', 'TOUR-101', 'MEMORACION', 'MT13'],
];
foreach ($wed as $s) {
    addSched($db, $rid, 'Wednesday', $s[0], $s[1], $s[2], $s[3], $s[4]);
}

echo "COMLAB6 (CON 104) Schedule Updated successfully!" . PHP_EOL;
?>
