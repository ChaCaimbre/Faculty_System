<?php
require_once 'config.php';
$db = getDB();

// 1. Ensure "2nd Semester" exist
$termName = '2nd Semester 2024-2025';
$res = $db->query("SELECT id FROM academic_terms WHERE name = '" . $db->real_escape_string($termName) . "'");
if ($res->num_rows == 0) {
    $db->query("INSERT INTO academic_terms (name, user_id, is_active) VALUES ('" . $db->real_escape_string($termName) . "', 1, 1)");
    $termId = $db->insert_id;
    // Set other terms of user 1 to inactive
    $db->query("UPDATE academic_terms SET is_active = 0 WHERE id != $termId AND user_id = 1");
} else {
    $termId = $res->fetch_assoc()['id'];
    $db->query("UPDATE academic_terms SET is_active = 1 WHERE id = $termId");
    $db->query("UPDATE academic_terms SET is_active = 0 WHERE id != $termId AND user_id = 1");
}

echo "Using Term: $termName (ID: $termId)\n";

// 2. Clear existing schedules for this term
$db->query("DELETE FROM schedules WHERE term_id = $termId");
echo "Cleared existing schedules for $termName.\n";

// 3. Ensure Rooms exist (same as update_master_sched.php)
$rooms = [
    'COMLAB1', 'COMLAB2', 'COMLAB3', 'COMLAB4',
    'COMLAB5 (CON 103)', 'COMLAB6 (CON 104)', 'COMLAB7 (CON 105)',
    'CHS (CON 101)', 'CISCO (CON 102)', 'OTHER ROOMS'
];

$roomMap = [];
foreach ($rooms as $roomName) {
    $res = $db->query("SELECT id FROM rooms WHERE name = '" . $db->real_escape_string($roomName) . "' AND user_id = 1");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO rooms (name, user_id) VALUES ('" . $db->real_escape_string($roomName) . "', 1)");
        $roomMap[$roomName] = $db->insert_id;
    } else {
        $roomMap[$roomName] = $res->fetch_assoc()['id'];
    }
}

// 4. Helper for adding schedule
function addSched($db, $roomMap, $roomName, $dayArr, $start, $end, $subCode, $section, $facName, $termId)
{
    if (!isset($roomMap[$roomName])) return;
    $rid = $roomMap[$roomName];

    // Ensure Faculty
    $facName = trim($facName) ?: 'TBA';
    $f_res = $db->query("SELECT id FROM faculty WHERE name = '" . $db->real_escape_string($facName) . "' AND user_id = 1");
    if ($f_res->num_rows == 0) {
        $db->query("INSERT INTO faculty (name, user_id) VALUES ('" . $db->real_escape_string($facName) . "', 1)");
        $fid = $db->insert_id;
    } else {
        $fid = $f_res->fetch_assoc()['id'];
    }

    // Ensure Subject
    $s_res = $db->query("SELECT id FROM subjects WHERE code = '" . $db->real_escape_string($subCode) . "' AND user_id = 1");
    if ($s_res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name, term_id, user_id) VALUES ('" . $db->real_escape_string($subCode) . "', '" . $db->real_escape_string($subCode) . "', $termId, 1)");
        $sid = $db->insert_id;
    } else {
        $sid = $s_res->fetch_assoc()['id'];
    }

    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, term_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    foreach ($dayArr as $day) {
        $stmt->bind_param("iiissssi", $fid, $sid, $rid, $day, $section, $start, $end, $termId);
        $stmt->execute();
    }
}

$monThu = ['Monday', 'Thursday'];
$tueFri = ['Tuesday', 'Friday'];
$wedOnly = ['Wednesday'];
$satOnly = ['Saturday'];

// --- DATA FROM IMAGES (using military format) ---

// Room: COMLAB1
addSched($db, $roomMap, 'COMLAB1', $monThu, '07:30:00', '09:00:00', 'IT-128', 'AI32', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '09:00:00', '10:30:00', 'IT-128L', 'AI32', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '10:30:00', '12:00:00', 'IT-130L', 'AI11', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '13:00:00', '14:30:00', 'IT-115', 'AI22', 'DURANGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '14:30:00', '16:00:00', 'IT-115L', 'AI25', 'DURANGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '16:00:00', '17:30:00', 'IT-105L', 'AI34', 'SAMPAYAN', $termId);
addSched($db, $roomMap, 'COMLAB1', $monThu, '17:30:00', '19:00:00', 'IT-112L', 'AI24', 'COMBINIDO', $termId);

addSched($db, $roomMap, 'COMLAB1', $tueFri, '07:30:00', '09:00:00', 'IT-128', 'AI31', 'FUNCION', $termId);
addSched($db, $roomMap, 'COMLAB1', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI31', 'FUNCION', $termId);
addSched($db, $roomMap, 'COMLAB1', $tueFri, '10:30:00', '12:00:00', 'IT-115L', 'AI21', 'FERNANDEZ', $termId);
addSched($db, $roomMap, 'COMLAB1', $tueFri, '14:30:00', '16:00:00', 'IT-112L', 'AI21', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $tueFri, '16:00:00', '17:30:00', 'IT-105L', 'AI35', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB1', $tueFri, '17:30:00', '19:00:00', 'IT-127L', 'AI31', 'CELESTIAL', $termId);

addSched($db, $roomMap, 'COMLAB1', $wedOnly, '09:00:00', '10:30:00', 'IT-115', 'AI25', 'DURANGO', $termId);
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '10:30:00', '12:00:00', 'IT-108', 'AI11', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '15:00:00', '17:00:00', 'IT-107', 'AI11', 'TIQUEN', $termId);
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '17:00:00', '19:00:00', 'IT-112', 'AI24', 'COMBINIDO', $termId);

addSched($db, $roomMap, 'COMLAB1', $satOnly, '08:00:00', '10:00:00', 'IT-127', 'AI35', 'MURILLO', $termId);

// Room: COMLAB2
addSched($db, $roomMap, 'COMLAB2', $monThu, '07:30:00', '09:00:00', 'IT-107', 'AI31', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $monThu, '09:00:00', '10:30:00', 'IT-107L', 'AI13', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $monThu, '10:30:00', '12:00:00', 'IT-108L', 'AI13', 'CINCO', $termId);
addSched($db, $roomMap, 'COMLAB2', $monThu, '13:00:00', '14:30:00', 'IT-105L', 'AI31', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB2', $monThu, '14:30:00', '16:00:00', 'IT-107L', 'AI14', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $monThu, '16:00:00', '17:00:00', 'IT-107', 'AI14', 'GALBAN', $termId); // 17:00 (Red)
addSched($db, $roomMap, 'COMLAB2', $monThu, '17:30:00', '19:00:00', 'TOUR-101L', 'MT13', 'MEMORACION', $termId);

addSched($db, $roomMap, 'COMLAB2', $tueFri, '07:30:00', '09:00:00', 'IT-127L', 'AI34', 'MURILLO', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '09:00:00', '10:30:00', 'IT-107L', 'AI15', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '10:30:00', '12:00:00', 'IT-108L', 'AI14', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '13:00:00', '14:30:00', 'GE-113', 'SM31', 'QUISUMBING', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '14:30:00', '16:00:00', 'IT-115L', 'AI24', 'FERNANDEZ', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '16:00:00', '17:30:00', 'IT-127L', 'AI32', 'LAGONOY', $termId);
addSched($db, $roomMap, 'COMLAB2', $tueFri, '17:30:00', '19:00:00', 'TOUR-101L', 'MT12', 'ALMENARIO', $termId);

addSched($db, $roomMap, 'COMLAB2', $wedOnly, '08:00:00', '10:00:00', 'IT-112', 'AI22', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '10:00:00', '12:00:00', 'IT-112', 'AI23', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '13:00:00', '15:00:00', 'IT-107', 'AI15', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '15:00:00', '17:00:00', 'IT-127', 'AI33', 'LAGONOY', $termId);
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '17:00:00', '19:00:00', 'IT-127', 'AI31', 'CELESTIAL', $termId);

addSched($db, $roomMap, 'COMLAB2', $satOnly, '13:00:00', '16:00:00', 'IT-129', 'AI35', 'DIAZ', $termId); // 13:00-16:00 (Red)

// Room: COMLAB3
addSched($db, $roomMap, 'COMLAB3', $monThu, '08:00:00', '09:00:00', 'IT-128', 'AI31', 'FUNCION', $termId); // 08:00 (Red)
addSched($db, $roomMap, 'COMLAB3', $monThu, '09:00:00', '10:30:00', 'IT-128L', 'AI34', 'FUNCION', $termId);
addSched($db, $roomMap, 'COMLAB3', $monThu, '10:30:00', '12:00:00', 'IT-105L', 'AI32', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB3', $monThu, '13:00:00', '14:30:00', 'IT-112L', 'AI25', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB3', $monThu, '14:30:00', '15:30:00', 'IT-105', 'AI32', 'CABANGON', $termId); // 15:30 (Red)
addSched($db, $roomMap, 'COMLAB3', $monThu, '16:00:00', '17:30:00', 'IT-127L', 'AI33', 'LAGONOY', $termId);
addSched($db, $roomMap, 'COMLAB3', $monThu, '17:30:00', '19:00:00', 'IT-127L', 'AI35', 'MURILLO', $termId);

addSched($db, $roomMap, 'COMLAB3', $tueFri, '07:30:00', '09:00:00', 'IT-107L', 'AI11', 'TIQUEN', $termId);
addSched($db, $roomMap, 'COMLAB3', $tueFri, '09:00:00', '10:30:00', 'IT-107L', 'AI12', 'TIQUEN', $termId);
addSched($db, $roomMap, 'COMLAB3', $tueFri, '10:30:00', '12:00:00', 'IT-124', 'AI33', 'FUNCION', $termId);
addSched($db, $roomMap, 'COMLAB3', $tueFri, '13:00:00', '14:30:00', 'IT-108', 'AI15', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB3', $tueFri, '14:30:00', '15:30:00', 'IT-108', 'AI15', 'ORMENETA', $termId); // 15:30 (Red)
addSched($db, $roomMap, 'COMLAB3', $tueFri, '16:00:00', '17:30:00', 'IT-115L', 'AI23', 'FERNANDEZ', $termId);

addSched($db, $roomMap, 'COMLAB3', $wedOnly, '08:00:00', '10:00:00', 'IT-108', 'AI31', 'CINCO', $termId);
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '10:00:00', '12:00:00', 'IT-107', 'AI12', 'TIQUEN', $termId);
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '13:00:00', '15:00:00', 'IT-108', 'AI14', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '15:00:00', '17:00:00', 'IT-129', 'AI33', 'DABLEO', $termId);

// Room: COMLAB4
addSched($db, $roomMap, 'COMLAB4', $monThu, '07:30:00', '09:00:00', 'IT-105L', 'AI33', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB4', $monThu, '09:00:00', '10:30:00', 'IT-112L', 'AI22', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB4', $monThu, '10:30:00', '12:00:00', 'IT-112L', 'AI23', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB4', $monThu, '13:00:00', '14:30:00', 'IT-108L', 'AI12', 'ORMENETA', $termId);
addSched($db, $roomMap, 'COMLAB4', $monThu, '14:30:00', '16:00:00', 'IT-120L', 'AI21', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB4', $monThu, '16:00:00', '17:00:00', 'IT-120L', 'AI21', 'DALAN', $termId); // 17:00 (Red)

addSched($db, $roomMap, 'COMLAB4', $tueFri, '08:00:00', '09:00:00', 'IT-120', 'AI24', 'TIBE', $termId); // 08:00 (Red)
addSched($db, $roomMap, 'COMLAB4', $tueFri, '09:00:00', '10:30:00', 'IT-112L', 'AI24', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB4', $tueFri, '10:30:00', '12:00:00', 'GE-113', 'AS22', 'GALBAN', $termId);
addSched($db, $roomMap, 'COMLAB4', $tueFri, '13:00:00', '14:30:00', 'IT-108L', 'AI22', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB4', $tueFri, '14:30:00', '15:30:00', 'IT-120', 'AI22', 'TIBE', $termId); // 15:30 (Red)
addSched($db, $roomMap, 'COMLAB4', $tueFri, '16:00:00', '17:00:00', 'IT-108', 'AI12', 'ORMENETA', $termId); // 17:00 (Red)

addSched($db, $roomMap, 'COMLAB4', $wedOnly, '08:00:00', '10:00:00', 'IT-112', 'AI21', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '10:00:00', '12:00:00', 'IT-112', 'AI25', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '13:00:00', '15:00:00', 'IT-127', 'AI32', 'LAGONOY', $termId);
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '15:00:00', '17:00:00', 'IT-105', 'AI34', 'SAMPAYAN', $termId);
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '17:00:00', '19:00:00', 'IT-127', 'AI34', 'MURILLO', $termId);

// Room: COMLAB5 (CON 103)
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '07:30:00', '09:00:00', 'GE-113', 'AS21', 'VERECIO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '09:00:00', '10:30:00', 'GE-113', 'MH21', 'TIQUEN', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '10:30:00', '12:00:00', 'IT-124', 'AI34', 'APOLINAR', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '13:00:00', '14:30:00', 'SPT-104', 'AL21', 'NAVARRO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '14:30:00', '16:00:00', 'IT-129L', 'AI31', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '16:00:00', '17:30:00', 'IT-126', 'AI32', 'VERECIO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '17:30:00', '19:00:00', 'IT-129L', 'AI33', 'DABLEO', $termId);

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '07:30:00', '09:00:00', 'IT-128L', 'AI36', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI35', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '10:30:00', '12:00:00', 'SPT-111', 'AL31', 'NAVARRO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '13:00:00', '14:30:00', 'LIS-106', 'AL21', 'NAVARRO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '14:30:00', '16:00:00', 'SPT-110', 'AL21', 'NAVARRO', $termId);
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '17:30:00', '19:00:00', 'IT-124', 'AI35', 'MEMORACION', $termId);

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '09:00:00', '12:00:00', 'LIS-112', 'AL31', 'NAVARRO', $termId); // 09:00-12:00 (Red)
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '13:00:00', '16:00:00', 'GE-107', 'AL21', 'NAVARRO', $termId); // 13:00-16:00 (Red)
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '17:00:00', '19:00:00', 'TOUR-101', 'MT12', 'ALMENARIO', $termId);

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $satOnly, '09:00:00', '12:00:00', 'LIS-104', 'AL21', 'SASI', $termId); // 09:00-12:00 (Red)

// Room: COMLAB6 (CON 104)
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '07:30:00', '09:00:00', 'IT-104', 'AI11', 'APOLINAR', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '09:00:00', '10:30:00', 'IT-104', 'AI12', 'APOLINAR', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '10:30:00', '12:00:00', 'IT-104', 'AI14', 'LAURENTE', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '13:00:00', '14:30:00', 'IT-104', 'AI13', 'SAMPAYAN', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '14:30:00', '16:00:00', 'IT-104', 'AI15', 'SAMPAYAN', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '17:30:00', '19:00:00', 'GE-113', 'MH22', 'NICOLAS', $termId);

addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '07:30:00', '10:30:00', 'IT-134', 'AI41', 'LAURENTE', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '13:00:00', '14:30:00', 'IT-120L', 'AI23', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '14:30:00', '16:00:00', 'IT-120', 'AI23', 'DALAN', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '17:30:00', '19:00:00', 'GE-113', 'MH23', 'NICOLAS', $termId);

addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '08:00:00', '10:00:00', 'IT-115', 'AI24', 'FERNANDEZ', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '10:00:00', '12:00:00', 'IT-128', 'AI33', 'CALUZA', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '15:00:00', '17:00:00', 'IT-115', 'AI21', 'FERNANDEZ', $termId);
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '17:00:00', '19:00:00', 'TOUR-101', 'MT13', 'MEMORACION', $termId);

// Room: COMLAB7 (CON 105)
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '07:30:00', '09:00:00', 'IT-124', 'AI31', 'CALUZA', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '09:00:00', '10:30:00', 'IT-120L', 'AI25', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '10:30:00', '12:00:00', 'IT-120', 'AI25', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '13:00:00', '16:00:00', 'IT-134', 'AI42', 'CINCO', $termId);

addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '07:30:00', '09:00:00', 'IT-124', 'AI32', 'CALUZA', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI33', 'CALUZA', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '10:30:00', '12:00:00', 'LIS ICT-104A/L', 'AL11', 'AMAGO', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '14:30:00', '16:00:00', 'TOUR-101L', 'MT11', 'SAMPAYAN', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '16:00:00', '17:00:00', 'TOUR-101', 'MT11', 'SAMPAYAN', $termId); // 17:00 (Red)
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '17:30:00', '19:00:00', 'GE-113', 'SM32', 'MORETO', $termId);

addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '08:00:00', '10:00:00', 'IT-105', 'AI31', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '10:00:00', '12:00:00', 'IT-129', 'AI31', 'TIBE', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '13:00:00', '15:00:00', 'IT-105', 'AI33', 'CABANGON', $termId);
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '15:00:00', '17:00:00', 'IT-115', 'AI22', 'DURANGO', $termId);

// Room: CHS (CON 101)
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '07:30:00', '09:00:00', 'GE-113', 'MH24', 'TIQUEN', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '09:00:00', '10:30:00', 'IT-130L', 'AI11', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '10:30:00', '12:00:00', 'IT-130L', 'AI15', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '13:30:00', '14:30:00', 'IT-129', 'AI34', 'TURCO', $termId); // 13:30 (Red)
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '14:30:00', '16:00:00', 'IT-129L', 'AI34', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '17:30:00', '19:00:00', 'IT-130L', 'AI14', 'VILLAFUERTE', $termId);

addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '08:00:00', '09:00:00', 'IT-130', 'AI12', 'DURANGO', $termId); // 08:00 (Red)
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI32', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '10:30:00', '12:00:00', 'IT-130L', 'AI12', 'DURANGO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '14:30:00', '16:00:00', 'IT-130L', 'AI13', 'DURANGO', $termId);

addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '08:00:00', '10:00:00', 'IT-130', 'AI11', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '10:00:00', '12:00:00', 'IT-129', 'AI32', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '13:00:00', '15:00:00', 'IT-130', 'AI13', 'DURANGO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '15:00:00', '17:00:00', 'IT-130', 'AI15', 'TURCO', $termId);
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '17:00:00', '19:00:00', 'IT-130', 'AI14', 'VILLAFUERTE', $termId);

echo "Successfully populated Second Semester schedule from image data!\n";
?>
